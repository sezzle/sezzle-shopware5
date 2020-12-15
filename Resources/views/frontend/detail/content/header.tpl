{extends file='parent:frontend/detail/content/header.tpl'}

{*Sezzle Widget integration*}
{block name='frontend_detail_index_header'}
    {if $isWidgetActiveForPDP && $merchantUUID}
        <script>
            console.log("Sezzle Widget rendering.");
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://widget.sezzle.com/v1/javascript/price-widget?uuid={$merchantUUID}';
            document.body.append(script);
            console.log("Sezzle Widget rendered.");
        </script>
    {/if}
    {$smarty.block.parent}
{/block}

