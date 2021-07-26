{extends file='parent:frontend/checkout/cart.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_index_content'}
    {if $isWidgetActiveForCart && $widgetURL}
        <script type="text/javascript">
            document.sezzleConfig = {
                language: '{$sezzleWidgetLanguage}',
                'configGroups': [
                    {
                        targetXPath: '.entry--total/.entry--value',
                        renderToPath: '../../LI-4'
                    }
                ]
            }

            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = '{$widgetURL}';
            document.body.append(script);
        </script>
    {/if}
    {$smarty.block.parent}
{/block}

