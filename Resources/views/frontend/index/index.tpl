{extends file='parent:frontend/index/index.tpl'}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    {if ($isSezzleWidgetActiveForPDP || $isSezzleWidgetActiveForCart) && $sezzleWidgetURL}
        <script type="text/javascript">
            document.sezzleConfig = {
                language: '{$sezzleWidgetLanguage}',
                'configGroups': [
                    {if $isSezzleWidgetActiveForPDP}
                    {
                        targetXPath: '.price--content',
                        renderToPath: '.price--content'
                    }
                    {if $isSezzleWidgetActiveForCart},{/if}
                    {/if}
                    {if $isSezzleWidgetActiveForCart}
                    {
                        targetXPath: '.basket--footer/.entry--total/.entry--value',
                        renderToPath: '../../LI-4'
                    },
                    {
                        targetXPath: '.container--ajax-cart/.prices--articles-amount',
                        renderToPath: '.container--ajax-cart/.prices--container',
                        alignment: 'right'
                    }
                    {/if}
                ]
            };
            const sezzleScript = document.createElement('script');
            sezzleScript.type = 'text/javascript';
            sezzleScript.src = '{$sezzleWidgetURL}';
            document.body.append(sezzleScript);
        </script>
    {/if}
{/block}