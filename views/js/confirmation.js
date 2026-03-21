document.addEventListener('DOMContentLoaded', function(){
    $('.copyBarcode').click(function(){
        navigator.clipboard.writeText($('.barcode').text()).then(
            () => {
                $(this).text('Copiado com sucesso!');
            },
            () => {
                $(this).text('Ocorreu um erro, tente novamente mais tarde.');
            }
        );

        let oldText = $(this).text();

        let that = this;
        setTimeout(function(){
            $(that).text(oldText);
        }, 4000);

        return false;
    });

    $('.copyPix').click(function(){

        navigator.clipboard.writeText($('#pix-qrcode').attr('data-pix-data')).then(
            () => {
                $(this).text('Copiado com sucesso!');
            },
            () => {
                $(this).text('Ocorreu um erro, tente novamente mais tarde.');
            }
        );

        let oldText = $(this).text();

        let that = this;
        setTimeout(function(){
            $(that).text(oldText);
        }, 4000);

        return false;
    });

    (function initAgPagSeguroOrderWatcher(){
        const config = window.agpagseguroConfirmation || {};
        if (!config.enabled || !config.sseUrl || typeof EventSource === 'undefined') {
            return;
        }

        const waitingAlert = document.getElementById('agpagseguro-waiting-alert');
        const stateLabel = document.getElementById('agpagseguro-order-current-state');

        if (waitingAlert && config.waitingMessage) {
            waitingAlert.textContent = config.waitingMessage;
        }

        let retries = 0;
        const maxRetries = 3;
        let source;

        const dispatchApprovedEvent = function(){
            if (!config.browserEventName || typeof window.CustomEvent !== 'function') {
                return;
            }

            window.dispatchEvent(new CustomEvent(config.browserEventName, {
                detail: {
                    orderId: config.orderId,
                    status: 'approved'
                }
            }));
        };

        const forceFullReload = function(){
            const reloadUrl = new URL(window.location.href.split('#')[0]);
            reloadUrl.searchParams.set('agpagseguro_refresh', Date.now().toString());
            const delay = Number.isFinite(parseInt(config.reloadDelay, 10)) ? parseInt(config.reloadDelay, 10) : 0;
            setTimeout(function(){
                window.location.replace(reloadUrl.toString());
            }, Math.max(0, delay));
        };

        const onApproved = function(){
            if (waitingAlert) {
                waitingAlert.style.display = 'none';
            }

            if (stateLabel && config.approvedStateLabel) {
                stateLabel.textContent = config.approvedStateLabel;
            }

            dispatchApprovedEvent();
            if (config.autoReload !== false) {
                forceFullReload();
            }
        };

        const connect = function(){
            source = new EventSource(config.sseUrl);

            source.addEventListener('approved', function(){
                source.close();
                onApproved();
            });

            source.addEventListener('waiting', function(){
                if (waitingAlert) {
                    waitingAlert.style.display = '';
                }
            });

            source.addEventListener('timeout', function(){
                source.close();
                if (waitingAlert) {
                    waitingAlert.classList.add('alert-warning');
                    waitingAlert.classList.remove('alert-info');
                    waitingAlert.textContent = config.timeoutMessage || config.waitingMessage || '';
                }
            });

            source.onerror = function(){
                if (source.readyState === EventSource.CLOSED && retries < maxRetries) {
                    retries += 1;
                    setTimeout(connect, 2000 * retries);
                } else {
                    source.close();
                }
            };
        };

        connect();
    })();
});
