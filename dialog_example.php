<?php
global $APPLICATION;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пример использования диалога пошагового процесса");
\Bitrix\Main\UI\Extension::load("ui.stepprocessing"); ?>
<script>
    let bitProcessing = new BX.UI.StepProcessing.Process({
        id : "example",
        controller : "chelbit:bp.Example",
        messages : {
            DialogTitle : "Пример диалога пошагового процесса",
            DialogSummary : "После запуска произойдет запуск фоновго процесса"
        },
        queue : [
            {
                action : "first",
                title : "Запуск первого шага",
                progressBarTitle : "Формирование массива с хещданными.."
            }
        ],
        showButtons : {
            stop : false,
            close : true,
            start : true
        }
    });

</script>
<style>
    #wrap54{
        display: flex;
        flex-direction: column;
        width: 500px;
    }
    .ui-btn{
        margin-bottom: 10px;
        margin-left: 0 !important;
    }
</style>
<div id="wrap54">
    <button onclick="bitProcessing.showDialog()" class="ui-btn ui-btn-primary">Запустимть тестовый пример</button>

</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

