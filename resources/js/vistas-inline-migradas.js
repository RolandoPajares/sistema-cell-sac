/** Comportamientos migrados desde vistas PHP. */
/*  */

(() => {
    const page = document.querySelector('[data-menu-page]');
    if (!page) return;

    const open = (id) => {
        const dialog = document.getElementById(id);
        if (dialog && !dialog.open) dialog.showModal();
    };

    page.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => open(button.dataset.openDialog));
    });

    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    document.querySelectorAll('.tc-rbac-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    });

    const treeRows = [...page.querySelectorAll('[data-tree-key]')];
    const descendants = (key) => [...page.querySelectorAll(`[data-tree-parent="${key}"]`)];
    const setExpanded = (key, expanded) => {
        descendants(key).forEach((row) => {
            row.hidden = !expanded;
            if (!expanded && row.dataset.treeKey) setExpanded(row.dataset.treeKey, false);
        });
    };

    page.querySelectorAll('[data-tree-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            const icon = button.querySelector('i');
            icon?.classList.toggle('bi-chevron-down', expanded);
            icon?.classList.toggle('bi-chevron-right', !expanded);
            setExpanded(button.dataset.treeToggle, expanded);
        });
    });

    const filter = page.querySelector('[data-menu-filter]');
    const status = page.querySelector('[data-menu-status]');
    const apply = () => {
        const value = (filter?.value || '').toLowerCase().trim();
        const state = status?.value || '';
        page.querySelectorAll('[data-menu-row]').forEach((row) => {
            row.hidden = (value !== '' && !row.dataset.search.includes(value))
                || (state !== '' && row.dataset.status !== state);
        });
    };
    filter?.addEventListener('input', apply);
    status?.addEventListener('change', apply);
})();


/*  */

(() => { const page=document.querySelector('[data-assignment-page]'); if(!page)return; const open=(id)=>{const d=document.getElementById(id);if(d&&!d.open)d.showModal()}; page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>open(b.dataset.openDialog))); document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close())); document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()})); page.querySelector('[data-assignment-filter]')?.addEventListener('input',(e)=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-assignment-row]').forEach(r=>{r.hidden=v!==''&&!r.dataset.search.includes(v)})}); })();


/*  */

    (() => { const page = document.querySelector('[data-rbac-page]'); if (!page) return; page.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal())); document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close())); })();


/*  */

(()=>{const page=document.querySelector('[data-module-page]');if(!page)return;page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>{const d=document.getElementById(b.dataset.openDialog);if(d&&!d.open)d.showModal()}));document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close()));document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));page.querySelector('[data-module-filter]')?.addEventListener('input',e=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-module-row]').forEach(r=>r.hidden=v!==''&&!r.dataset.search.includes(v))});})();


/*  */

        (() => {
            const form = document.querySelector('[data-report-filter-form]');
            const tabs = [...document.querySelectorAll('[data-report-tab]')];
            const panels = [...document.querySelectorAll('[data-report-panel]')];
            const viewInput = form?.querySelector('[data-report-view]');
            const period = form?.querySelector('[data-report-period]');
            const from = form?.querySelector('[data-report-from]');
            const to = form?.querySelector('[data-report-to]');

            const activate = (view, updateUrl = true) => {
                const selectedTab = tabs.find((tab) => tab.dataset.reportTab === view);
                if (!selectedTab) {
                    return;
                }

                tabs.forEach((tab) => {
                    const selected = tab === selectedTab;
                    tab.classList.toggle('is-active', selected);
                    tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    const selected = panel.dataset.reportPanel === view;
                    panel.hidden = !selected;
                    panel.classList.toggle('is-active', selected);
                });
                if (viewInput) {
                    viewInput.value = view;
                }
                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('vista', view);
                    window.history.replaceState({}, '', url);
                }
            };

            tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.reportTab)));
            document.querySelectorAll('[data-report-tab-link]').forEach((link) => {
                link.addEventListener('click', () => activate(link.dataset.reportTabLink));
            });
            form?.addEventListener('submit', () => {
                if (viewInput) {
                    viewInput.value = document.querySelector('[data-report-tab].is-active')?.dataset.reportTab || 'resumen';
                }
            });
            period?.addEventListener('change', () => {
                if (period.value === 'custom') {
                    return;
                }
                if (period.value === 'all') {
                    from?.removeAttribute('name');
                    to?.removeAttribute('name');
                    form?.submit();
                    return;
                }
                const days = Number(period.value);
                const today = new Date();
                const start = new Date(today);
                start.setDate(today.getDate() - (days - 1));
                const toIso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
                if (from && to) {
                    from.value = toIso(start);
                    to.value = toIso(today);
                }
                form?.submit();
            });
        })();
    

/*  */

        (() => {
            const dialog = document.querySelector('[data-quotation-dialog]');
            if (!dialog) {
                return;
            }

            const contenido = {
                enviar: {
                    titulo: 'Enviar cotizacion manualmente',
                    descripcion: 'Descarga el PDF y envialo desde tu correo.',
                    mensaje: 'Technology Cell no envia correos automaticamente. Descarga el PDF, adjuntalo al mensaje y envialo al destinatario mostrado. Despues confirma aqui el envio realizado.',
                },
                convertir: {
                    titulo: 'Convertir cotizacion en venta',
                    descripcion: 'La venta se creara como borrador y no descontara existencias.',
                    mensaje: 'Selecciona el almacen autorizado. La venta quedara pendiente de confirmacion; el inventario se descontara solo al confirmar la venta.',
                },
            };
            const titulo = dialog.querySelector('[data-quotation-title]');
            const descripcion = dialog.querySelector('[data-quotation-description]');
            const mensaje = dialog.querySelector('[data-quotation-message]');
            const referencia = dialog.querySelector('[data-quotation-reference]');
            const cliente = dialog.querySelector('[data-quotation-customer]');
            const envioManual = dialog.querySelector('[data-manual-send]');
            const formulario = dialog.querySelector('[data-manual-send-form]');
            const descarga = dialog.querySelector('[data-quotation-download]');
            const correo = dialog.querySelector('[data-quotation-mailto]');
            const registro = dialog.querySelector('[data-record-manual-send]');
            const conversion = dialog.querySelector('[data-conversion-form]');
            const confirmarConversion = dialog.querySelector('[data-confirm-conversion]');
            const almacenConversion = dialog.querySelector('#almacen-conversion-cotizacion');
            const bloqueo = dialog.querySelector('[data-quotation-blocked]');

            document.querySelectorAll('[data-quotation-action]').forEach((boton) => {
                boton.addEventListener('click', () => {
                    const accion = boton.dataset.quotationAction ?? '';
                    const textos = contenido[accion];
                    if (!textos) {
                        return;
                    }
                    titulo.textContent = textos.titulo;
                    descripcion.textContent = textos.descripcion;
                    mensaje.textContent = textos.mensaje;
                    referencia.textContent = boton.dataset.reference ?? '—';
                    cliente.textContent = boton.dataset.customer ?? 'Cliente no disponible';
                    const esEnvio = accion === 'enviar';
                    envioManual.hidden = !esEnvio;
                    registro.hidden = !esEnvio;
                    conversion.hidden = esEnvio;
                    confirmarConversion.hidden = esEnvio;
                    bloqueo.hidden = true;
                    formulario.action = boton.dataset.send ?? '#';
                    conversion.action = boton.dataset.convert ?? '#';
                    const sedeId = boton.dataset.siteId ?? '';
                    Array.from(almacenConversion.options).forEach((opcion) => {
                        const visible = opcion.value === '' || opcion.dataset.siteId === sedeId;
                        opcion.hidden = !visible;
                        opcion.disabled = !visible;
                    });
                    if (almacenConversion.selectedOptions[0]?.hidden) {
                        almacenConversion.value = '';
                    }
                    descarga.href = boton.dataset.download ?? '#';
                    const asunto = encodeURIComponent(`Cotizacion ${boton.dataset.reference ?? ''}`);
                    const cuerpo = encodeURIComponent(`Adjunto la cotizacion ${boton.dataset.reference ?? ''}.`);
                    correo.href = `mailto:${encodeURIComponent(boton.dataset.recipient ?? '')}?subject=${asunto}&body=${cuerpo}`;
                    formulario.reset();
                    dialog.showModal();
                });
            });

            dialog.querySelectorAll('[data-close-quotation-dialog]').forEach((boton) => boton.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('click', (evento) => {
                if (evento.target === dialog) {
                    dialog.close();
                }
            });
        })();
    

/*  */

        (() => {
            const root = document.querySelector('[data-ventas-diseno]');
            if (!root) {
                return;
            }

            const resetDialog = (dialog) => {
                const status = dialog.querySelector('[data-oc-demo-status]');
                const error = dialog.querySelector('[data-oc-demo-error]');
                const confirm = dialog.querySelector('[data-oc-confirm-demo]');
                if (status) {
                    status.hidden = true;
                }
                if (error) {
                    error.hidden = true;
                }
                if (confirm) {
                    confirm.disabled = false;
                }
                dialog.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
            };

            root.querySelectorAll('[data-oc-open-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = root.querySelector(`[data-oc-visual-dialog="${button.dataset.ocOpenModal}"]`);
                    if (dialog) {
                        resetDialog(dialog);
                        dialog.showModal();
                    }
                });
            });

            root.querySelectorAll('[data-oc-open-detail]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = root.querySelector('[data-oc-detail-dialog]');
                    const fields = dialog?.querySelector('[data-oc-detail-fields]');
                    const row = button.closest('tr');
                    if (!dialog || !fields || !row) {
                        return;
                    }
                    fields.replaceChildren();
                    const headings = [...root.querySelectorAll('.tc-oc__table-wrap thead th')].map((heading) => heading.textContent.trim()).filter(Boolean);
                    [...row.cells].slice(0, Math.max(0, row.cells.length - 1)).forEach((cell, index) => {
                        if (!headings[index]) {
                            return;
                        }
                        const term = document.createElement('dt');
                        const value = document.createElement('dd');
                        term.textContent = headings[index];
                        value.textContent = cell.textContent.trim();
                        fields.append(term, value);
                    });
                    dialog.showModal();
                });
            });

            root.querySelectorAll('[data-oc-confirm-demo]').forEach((button) => {
                const dialog = button.closest('dialog');
                const error = dialog?.querySelector('[data-oc-demo-error]');
                const status = dialog?.querySelector('[data-oc-demo-status]');
                dialog?.querySelectorAll('[required]').forEach((field) => {
                    field.addEventListener('input', () => field.removeAttribute('aria-invalid'));
                    field.addEventListener('change', () => field.removeAttribute('aria-invalid'));
                });
                button.addEventListener('click', () => {
                    const invalidFields = [...(dialog?.querySelectorAll('[required]') ?? [])].filter((field) => !field.checkValidity());
                    if (invalidFields.length > 0) {
                        invalidFields.forEach((field) => field.setAttribute('aria-invalid', 'true'));
                        error.textContent = 'Completa los campos obligatorios para continuar con esta demostración.';
                        error.hidden = false;
                        status.hidden = true;
                        invalidFields[0].focus();
                        return;
                    }
                    status.textContent = 'Integración pendiente: no se guardó la información ni se ejecutó ninguna operación comercial.';
                    status.hidden = false;
                    error.hidden = true;
                    button.disabled = true;
                });
            });

            root.querySelectorAll('[data-oc-close-modal]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            root.querySelectorAll('.tc-oc__dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            }));
        })();
    

/*  */

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
        

/*  */

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
        

/*  */

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
        

/*  */

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
        

// Envío de filtros sin eventos inline.
document.querySelectorAll('[data-submit-on-change]').forEach((control) => { control.addEventListener('change', () => control.form?.submit()); });

