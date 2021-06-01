{extends file='parent:frontend/detail/content/header.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_detail_index_header'}
    {if $isWidgetActiveForPDP && $widgetURL}
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

