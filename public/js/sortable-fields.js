function initSortable() {
    const el = document.getElementById('sortable-fields');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (el && csrfToken && !el.dataset.sortableInitialized) {
        el.dataset.sortableInitialized = "true";

        new Sortable(el, {
            animation: 150,
            onEnd: function () {
                const order = Array.from(el.children).map((li, index) => ({
                    id: parseInt(li.dataset.id),
                    position: index
                }));

                fetch(el.dataset.reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", initSortable);
document.addEventListener("turbo:load", initSortable);
