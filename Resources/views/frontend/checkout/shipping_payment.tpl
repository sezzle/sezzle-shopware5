{extends file='parent:frontend/checkout/shipping_payment.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_index_content'}
        <script type="text/javascript">
            var sezzleCron = function(){
                document.sezzleConfig = {
                    targetXPath: '.entry--total .entry--value',
                    renderToPath: '.payment_logo_Sezzle',
                    language: '{$sezzleWidgetLanguage}',
                    sezzleMerchantRegion: '{$sezzleMerchantRegion}'
                }
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
                if(typeof sezzleCheckoutRender === 'undefined') {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = '{link file='frontend/_public/src/js/installment-widget.js'}';
                    document.body.append(script);
                }else{
                    sezzleCheckoutRender();
                }
            };
            document.addEventListener('DOMContentLoaded', function(){
                setInterval(sezzleCron, 500);
            });
        </script>

    {$smarty.block.parent}
{/block}

