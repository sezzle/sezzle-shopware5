{extends file='parent:frontend/checkout/cart.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_index_content'}
    {if $isWidgetActiveForCart && $widgetURL}
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

