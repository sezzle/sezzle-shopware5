{extends file='parent:frontend/checkout/cart.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_index_content'}
    {if $isWidgetActiveForCart && $widgetURL}
        <script type="text/javascript">
            document.sezzleConfig = {
                targetXPath: '.entry--total/.entry--value',
                renderToPath: '../../LI-4'
            }
        </script>
        <script>
            console.log("Sezzle Widget rendering.");
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = '{$widgetURL}';
            document.body.append(script);
            console.log("Sezzle Widget rendered.");
        </script>
    {/if}
    {$smarty.block.parent}
{/block}

