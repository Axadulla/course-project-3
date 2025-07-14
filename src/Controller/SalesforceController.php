<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;
use App\Entity\User;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use App\Entity\SalesforceSubmission;
use App\Repository\SalesforceSubmissionRepository;

class SalesforceController extends AbstractController
{
    #[Route('/connect/salesforce', name: 'salesforce_connect')]
    public function connect(): RedirectResponse
    {
        $clientId = $_ENV['SALESFORCE_CLIENT_ID'];
        $redirectUri = $_ENV['SALESFORCE_CALLBACK_URL'];

        $authUrl = 'https://login.salesforce.com/services/oauth2/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id'     => $clientId,
                'redirect_uri'  => $redirectUri,
                'scope'         => 'api refresh_token',
            ]);

        return $this->redirect($authUrl);
    }

    #[Route('/oauth/salesforce/callback', name: 'salesforce_callback')]
    public function callback(Request $request): Response
    {
        $code = $request->query->get('code');
        if (!$code) {
            $this->addFlash('error', 'Ошибка: отсутствует код авторизации.');
            return $this->redirectToRoute('salesforce_form');
        }

        $clientId = $_ENV['SALESFORCE_CLIENT_ID'];
        $clientSecret = $_ENV['SALESFORCE_CLIENT_SECRET'];
        $redirectUri = $_ENV['SALESFORCE_CALLBACK_URL'];

        $httpClient = new Client();

        try {
            $response = $httpClient->post('https://login.salesforce.com/services/oauth2/token', [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri'  => $redirectUri,
                    'code'          => $code,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            $session = $request->getSession();
            $session->set('salesforce_access_token', $data['access_token']);
            $session->set('salesforce_instance_url', $data['instance_url']);

            $this->addFlash('success', '✅ Успешно подключено к Salesforce!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ошибка при подключении к Salesforce: ' . $e->getMessage());
        }

        $redirectTo = $request->query->get('redirectTo', $this->generateUrl('salesforce_form', ['id' => $this->getUser()->getId()]));
        return $this->redirect($redirectTo);

    }



    #[Route('/user/{id}/salesforce/form', name: 'salesforce_form')]
    public function form(User $user): Response
    {
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) && $user !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('salesforce/form.html.twig', ['user' => $user]);
    }



    #[Route('/salesforce/submit', name: 'salesforce_submit', methods: ['POST'])]
    public function submit(Request $request, LoggerInterface $logger, EntityManagerInterface $em): Response
    {
        $company = $request->request->get('company');
        $fullName = $request->request->get('fullName');
        $phone = $request->request->get('phone');
        $email = $request->request->get('email');
        $city = $request->request->get('city');

        $session = $request->getSession();
        $accessToken = $session->get('salesforce_access_token');
        $instanceUrl = $session->get('salesforce_instance_url');

        // 1. Проверка на наличие токена
        if (!$accessToken || !$instanceUrl) {
            $this->addFlash('error', '❌ Нет токена авторизации. Сначала подключитесь к Salesforce.');
            return $this->redirectToRoute('salesforce_form', ['id' => $this->getUser()->getId()]);
        }

        // 2. Разделение имени
        $parts = explode(' ', trim($fullName));
        $firstName = '';
        $lastName = '';

        if (count($parts) >= 2) {
            $firstName = $parts[0];
            $lastName = implode(' ', array_slice($parts, 1));
        } elseif (count($parts) === 1) {
            $lastName = $parts[0];
        }

        // 3. Обязательное наличие фамилии
        if (empty(trim($lastName))) {
            $this->addFlash('error', '❌ Пожалуйста, введите полное имя (имя и фамилия).');
            return $this->redirectToRoute('salesforce_form', ['id' => $this->getUser()->getId()]);
        }

        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ]
        ]);

        try {
            // 4. Создание аккаунта
            $accountResponse = $client->post($instanceUrl . '/services/data/v60.0/sobjects/Account', [
                'json' => [
                    'Name' => $company,
                    'BillingCity' => $city,
                    'Phone' => $phone,
                ]
            ]);
            $accountId = json_decode($accountResponse->getBody(), true)['id'];

            // 5. Создание контакта
            $client->post($instanceUrl . '/services/data/v60.0/sobjects/Contact', [
                'json' => [
                    'FirstName' => $firstName,
                    'LastName' => $lastName,
                    'Phone' => $phone,
                    'Email' => $email,
                    'AccountId' => $accountId,
                ]
            ]);

            // 6. Сохраняем только если Salesforce успешен
            $submission = new SalesforceSubmission();
            $submission->setCompany($company);
            $submission->setFullName($fullName);
            $submission->setPhone($phone);
            $submission->setEmail($email);
            $submission->setCity($city);
            $submission->setUser($this->getUser());
            $submission->setCreatedAt(new \DateTime());
            $em->persist($submission);
            $em->flush();

            return $this->redirectToRoute('salesforce_history', ['id' => $this->getUser()->getId()]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = json_decode($response->getBody(), true);
                $errorCode = $body[0]['errorCode'] ?? null;
                $message = $body[0]['message'] ?? 'Неизвестная ошибка';

                if ($errorCode === 'DUPLICATES_DETECTED') {
                    $this->addFlash('error', '❌ Такой аккаунт или контакт уже существует в Salesforce.');
                } else {
                    $this->addFlash('error', '❌ Ошибка Salesforce: ' . $message);
                }

                $logger->error('Salesforce ошибка: ' . $response->getBody());
            } else {
                $this->addFlash('error', '❌ Ошибка запроса к Salesforce (без ответа): ' . $e->getMessage());
                $logger->error('Ошибка запроса к Salesforce: ' . $e->getMessage());
            }
            return $this->redirectToRoute('salesforce_form', ['id' => $this->getUser()->getId()]);
        } catch (\Throwable $e) {
            $this->addFlash('error', '❌ Неизвестная ошибка при отправке в Salesforce.');
            $logger->error('Общая ошибка Salesforce: ' . $e->getMessage());
            return $this->redirectToRoute('salesforce_form', ['id' => $this->getUser()->getId()]);
        }
    }



    #[Route('/user/{id}/salesforce/history', name: 'salesforce_history')]
    public function history(User $user, SalesforceSubmissionRepository $repo): Response
    {
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) && $user !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $submissions = $repo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('salesforce/history.html.twig', [
            'submissions' => $submissions,
            'user' => $user,
        ]);
    }

    #[Route('/salesforce/bulk', name: 'salesforce_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');
        $action = $request->request->get('action');

        if (empty($ids)) {
            $this->addFlash('error', 'Ничего не выбрано.');
            return $this->redirectToRoute('salesforce_history', ['id' => $this->getUser()->getId()]);
        }

        if ($action === 'delete') {
            $repo = $em->getRepository(SalesforceSubmission::class);
            foreach ($ids as $id) {
                $submission = $repo->find($id);
                if ($submission && $submission->getUser() === $this->getUser()) {
                    $em->remove($submission);
                }
            }
            $em->flush();
            $this->addFlash('success', '✅ Записи удалены.');
        } elseif ($action === 'edit') {
            return $this->redirectToRoute('salesforce_edit', ['id' => $ids[0]]);
        }

        return $this->redirectToRoute('salesforce_history', ['id' => $this->getUser()->getId()]);
    }


    #[Route('/salesforce/delete/{id}', name: 'salesforce_delete', methods: ['POST'])]
    public function delete(SalesforceSubmission $submission, EntityManagerInterface $em): Response
    {
        if ($submission->getUser() !== $this->getUser() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($submission);
        $em->flush();

        $this->addFlash('success', '✅ Запись успешно удалена.');
        return $this->redirectToRoute('salesforce_history', ['id' => $this->getUser()->getId()]);
    }


    #[Route('/salesforce/edit/{id}', name: 'salesforce_edit')]
    public function edit(Request $request, SalesforceSubmission $submission, EntityManagerInterface $em): Response
    {
        if ($submission->getUser() !== $this->getUser() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $submission->setCompany($request->request->get('company'));
            $submission->setFullName($request->request->get('fullName'));
            $submission->setPhone($request->request->get('phone'));
            $submission->setEmail($request->request->get('email'));
            $submission->setCity($request->request->get('city'));
            $em->flush();

            $this->addFlash('success', '✅ Запись обновлена.');
            return $this->redirectToRoute('salesforce_history', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('salesforce/edit.html.twig', [
            'submission' => $submission,
        ]);
    }


}



