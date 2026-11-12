/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/vistas-inline-migradas.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/** Comportamientos migrados desde vistas PHP. */
/*  */

(() => {
    const page = document.querySelector('[data-menu-page]');
    // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
    if (!page) return;

    // Funcion: open concentra una tarea reutilizable del modulo.
    const open = (id) => {
        const dialog = document.getElementById(id);
        // Condicional: evalua "dialog && !dialog.open) dialog.showModal(" para decidir si ejecuta el bloque siguiente.
        if (dialog && !dialog.open) dialog.showModal();
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-dialog]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => open(button.dataset.openDialog));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('.tc-rbac-dialog').forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog) dialog.close(" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) dialog.close();
        });
    });

    const treeRows = [...page.querySelectorAll('[data-tree-key]')];
    // Funcion: descendants concentra una tarea reutilizable del modulo.
    const descendants = (key) => [...page.querySelectorAll(`[data-tree-parent="${key}"]`)];
    // Funcion: setExpanded concentra una tarea reutilizable del modulo.
    const setExpanded = (key, expanded) => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        descendants(key).forEach((row) => {
            row.hidden = !expanded;
            // Condicional: evalua "!expanded && row.dataset.treeKey) setExpanded(row.dataset.treeKey, false" para decidir si ejecuta el bloque siguiente.
            if (!expanded && row.dataset.treeKey) setExpanded(row.dataset.treeKey, false);
        });
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-tree-toggle]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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
    // Funcion: apply concentra una tarea reutilizable del modulo.
    const apply = () => {
        const value = (filter?.value || '').toLowerCase().trim();
        const state = status?.value || '';
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-menu-row]').forEach((row) => {
            row.hidden = (value !== '' && !row.dataset.search.includes(value))
                || (state !== '' && row.dataset.status !== state);
        });
    };
    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    filter?.addEventListener('input', apply);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    status?.addEventListener('change', apply);
})();


/*  */

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
(() => { const page=document.querySelector('[data-assignment-page]'); if(!page)return; const open=(id)=>{const d=document.getElementById(id);if(d&&!d.open)d.showModal()}; page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>open(b.dataset.openDialog))); document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close())); document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()})); page.querySelector('[data-assignment-filter]')?.addEventListener('input',(e)=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-assignment-row]').forEach(r=>{r.hidden=v!==''&&!r.dataset.search.includes(v)})}); })();


/*  */

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    (() => { const page = document.querySelector('[data-rbac-page]'); if (!page) return; page.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal())); document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close())); })();


/*  */

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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

            // Funcion: activate concentra una tarea reutilizable del modulo.
            const activate = (view, updateUrl = true) => {
                // Funcion: selectedTab concentra una tarea reutilizable del modulo.
                const selectedTab = tabs.find((tab) => tab.dataset.reportTab === view);
                // Condicional: evalua "!selectedTab" para decidir si ejecuta el bloque siguiente.
                if (!selectedTab) {
                    return;
                }

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                tabs.forEach((tab) => {
                    const selected = tab === selectedTab;
                    tab.classList.toggle('is-active', selected);
                    tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                panels.forEach((panel) => {
                    const selected = panel.dataset.reportPanel === view;
                    panel.hidden = !selected;
                    panel.classList.toggle('is-active', selected);
                });
                // Condicional: evalua "viewInput" para decidir si ejecuta el bloque siguiente.
                if (viewInput) {
                    viewInput.value = view;
                }
                // Condicional: evalua "updateUrl" para decidir si ejecuta el bloque siguiente.
                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('vista', view);
                    window.history.replaceState({}, '', url);
                }
            };

            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.reportTab)));
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            document.querySelectorAll('[data-report-tab-link]').forEach((link) => {
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                link.addEventListener('click', () => activate(link.dataset.reportTabLink));
            });
            // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
            form?.addEventListener('submit', () => {
                // Condicional: evalua "viewInput" para decidir si ejecuta el bloque siguiente.
                if (viewInput) {
                    viewInput.value = document.querySelector('[data-report-tab].is-active')?.dataset.reportTab || 'resumen';
                }
            });
            // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
            period?.addEventListener('change', () => {
                // Condicional: evalua "period.value === 'custom'" para decidir si ejecuta el bloque siguiente.
                if (period.value === 'custom') {
                    return;
                }
                // Condicional: evalua "period.value === 'all'" para decidir si ejecuta el bloque siguiente.
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
                // Funcion: toIso concentra una tarea reutilizable del modulo.
                const toIso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
                // Condicional: evalua "from && to" para decidir si ejecuta el bloque siguiente.
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
            // Condicional: evalua "!dialog" para decidir si ejecuta el bloque siguiente.
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

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            document.querySelectorAll('[data-quotation-action]').forEach((boton) => {
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                boton.addEventListener('click', () => {
                    const accion = boton.dataset.quotationAction ?? '';
                    const textos = contenido[accion];
                    // Condicional: evalua "!textos" para decidir si ejecuta el bloque siguiente.
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
                    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                    Array.from(almacenConversion.options).forEach((opcion) => {
                        const visible = opcion.value === '' || opcion.dataset.siteId === sedeId;
                        opcion.hidden = !visible;
                        opcion.disabled = !visible;
                    });
                    // Condicional: evalua "almacenConversion.selectedOptions[0]?.hidden" para decidir si ejecuta el bloque siguiente.
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

            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            dialog.querySelectorAll('[data-close-quotation-dialog]').forEach((boton) => boton.addEventListener('click', () => dialog.close()));
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            dialog.addEventListener('click', (evento) => {
                // Condicional: evalua "evento.target === dialog" para decidir si ejecuta el bloque siguiente.
                if (evento.target === dialog) {
                    dialog.close();
                }
            });
        })();
    

/*  */

        (() => {
            const root = document.querySelector('[data-ventas-diseno]');
            // Condicional: evalua "!root" para decidir si ejecuta el bloque siguiente.
            if (!root) {
                return;
            }

            // Funcion: resetDialog concentra una tarea reutilizable del modulo.
            const resetDialog = (dialog) => {
                const status = dialog.querySelector('[data-oc-demo-status]');
                const error = dialog.querySelector('[data-oc-demo-error]');
                const confirm = dialog.querySelector('[data-oc-confirm-demo]');
                // Condicional: evalua "status" para decidir si ejecuta el bloque siguiente.
                if (status) {
                    status.hidden = true;
                }
                // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
                if (error) {
                    error.hidden = true;
                }
                // Condicional: evalua "confirm" para decidir si ejecuta el bloque siguiente.
                if (confirm) {
                    confirm.disabled = false;
                }
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                dialog.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
            };

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            root.querySelectorAll('[data-oc-open-modal]').forEach((button) => {
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                button.addEventListener('click', () => {
                    const dialog = root.querySelector(`[data-oc-visual-dialog="${button.dataset.ocOpenModal}"]`);
                    // Condicional: evalua "dialog" para decidir si ejecuta el bloque siguiente.
                    if (dialog) {
                        resetDialog(dialog);
                        dialog.showModal();
                    }
                });
            });

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            root.querySelectorAll('[data-oc-open-detail]').forEach((button) => {
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                button.addEventListener('click', () => {
                    const dialog = root.querySelector('[data-oc-detail-dialog]');
                    const fields = dialog?.querySelector('[data-oc-detail-fields]');
                    const row = button.closest('tr');
                    // Condicional: evalua "!dialog || !fields || !row" para decidir si ejecuta el bloque siguiente.
                    if (!dialog || !fields || !row) {
                        return;
                    }
                    fields.replaceChildren();
                    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
                    const headings = [...root.querySelectorAll('.tc-oc__table-wrap thead th')].map((heading) => heading.textContent.trim()).filter(Boolean);
                    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                    [...row.cells].slice(0, Math.max(0, row.cells.length - 1)).forEach((cell, index) => {
                        // Condicional: evalua "!headings[index]" para decidir si ejecuta el bloque siguiente.
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

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            root.querySelectorAll('[data-oc-confirm-demo]').forEach((button) => {
                const dialog = button.closest('dialog');
                const error = dialog?.querySelector('[data-oc-demo-error]');
                const status = dialog?.querySelector('[data-oc-demo-status]');
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                dialog?.querySelectorAll('[required]').forEach((field) => {
                    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
                    field.addEventListener('input', () => field.removeAttribute('aria-invalid'));
                    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
                    field.addEventListener('change', () => field.removeAttribute('aria-invalid'));
                });
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                button.addEventListener('click', () => {
                    // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
                    const invalidFields = [...(dialog?.querySelectorAll('[required]') ?? [])].filter((field) => !field.checkValidity());
                    // Condicional: evalua "invalidFields.length > 0" para decidir si ejecuta el bloque siguiente.
                    if (invalidFields.length > 0) {
                        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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

            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            root.querySelectorAll('[data-oc-close-modal]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            root.querySelectorAll('.tc-oc__dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
                // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
                if (event.target === dialog) {
                    dialog.close();
                }
            }));
        })();
    

/*  */

            (() => {
                const page = document.querySelector('[data-admin-orders-page]');

                // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
                if (!page) {
                    return;
                }

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.addEventListener('click', (event) => {
                        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
        

/*  */

            (() => {
                const page = document.querySelector('[data-admin-orders-page]');

                // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
                if (!page) {
                    return;
                }

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.addEventListener('click', (event) => {
                        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
        

/*  */

            (() => {
                const page = document.querySelector('[data-admin-orders-page]');

                // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
                if (!page) {
                    return;
                }

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.addEventListener('click', (event) => {
                        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
        

/*  */

            (() => {
                const page = document.querySelector('[data-admin-orders-page]');

                // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
                if (!page) {
                    return;
                }

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    dialog.addEventListener('click', (event) => {
                        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
        

// Envío de filtros sin eventos inline.
// Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
document.querySelectorAll('[data-submit-on-change]').forEach((control) => { control.addEventListener('change', () => control.form?.submit()); });

