<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;
use App\Entity\User;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;



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
    public function submit(Request $request, LoggerInterface $logger, int $id): Response
    {
        $company = $request->request->get('company');
        $fullName = $request->request->get('fullName');
        $phone = $request->request->get('phone');
        $email = $request->request->get('email');
        $city = $request->request->get('city');

        $session = $request->getSession();
        $accessToken = $session->get('salesforce_access_token');
        $instanceUrl = $session->get('salesforce_instance_url');

        if (!$accessToken || !$instanceUrl) {
            $this->addFlash('error', '❌ Нет токена авторизации. Сначала подключитесь к Salesforce.');
            return $this->redirectToRoute('salesforce_form', ['id' => $this->getUser()->getId()]);
        }

        $parts = explode(' ', $fullName);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ]
        ]);

        try {
            $accountResponse = $client->post($instanceUrl . '/services/data/v60.0/sobjects/Account', [
                'json' => [
                    'Name' => $company,
                    'BillingCity' => $city,
                    'Phone' => $phone,
                ]
            ]);
            $accountId = json_decode($accountResponse->getBody(), true)['id'];

            $client->post($instanceUrl . '/services/data/v60.0/sobjects/Contact', [
                'json' => [
                    'FirstName' => $firstName,
                    'LastName' => $lastName,
                    'Phone' => $phone,
                    'Email' => $email,
                    'AccountId' => $accountId,
                ]
            ]);

            $this->addFlash('success', '✅ Данные успешно отправлены в Salesforce. Account и Contact созданы!');
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
        } catch (\Throwable $e) {
            $this->addFlash('error', '❌ Неизвестная ошибка при отправке в Salesforce.');
            $logger->error('Общая ошибка Salesforce: ' . $e->getMessage());
        }

        return $this->redirectToRoute('salesforce_form', ['id' => $id]);
    }

}



