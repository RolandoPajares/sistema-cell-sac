(() => {
                const page = document.querySelector('[data-admin-orders-page]');

                if (!page) {
                    return;
                }

                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    dialog.addEventListener('click', (event) => {
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
