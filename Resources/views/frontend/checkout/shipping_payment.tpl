{extends file='parent:frontend/checkout/shipping_payment.tpl'}

{*Sezzle installment widget integration*}
{block name='frontend_index_content'}
        <script type="text/javascript">
            var sezzleCron = function(){
                var element = document.querySelector('.payment_logo_Sezzle');
                if(!element){
                    return;
                }

                if( document.getElementById('sezzle-installment-widget-box')){
                    return;
                }

                var container = document.createElement('div');
                container.id = 'sezzle-installment-widget-box';
                element.append(container);

                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = 'https://checkout-sdk.sezzle.com/installment-widget.min.js';
                script.onload = function () {
                    new SezzleInstallmentWidget({
                       'merchantLocale': 'US',
                       'platform': 'shopware'
                    });
                };
                document.body.append(script);
            };
            document.addEventListener('DOMContentLoaded', function(){
                setInterval(sezzleCron, 500);
            });
        </script>

    {$smarty.block.parent}
{/block}

