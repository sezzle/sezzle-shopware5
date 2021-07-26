{extends file='parent:frontend/detail/content/header.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_detail_index_header'}
    {if $isWidgetActiveForPDP && $widgetURL}
        <script type="text/javascript">
            document.sezzleConfig = {
                language: '{$sezzleWidgetLanguage}',
                'configGroups': [
                    {
                        targetXPath: '.price--content',
                        renderToPath: '.price--content'
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

