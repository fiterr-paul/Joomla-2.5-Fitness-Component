<script type="text/javascript">  
$(document).ready(function() {
        var DATA_FEED_URL = "<?php echo $datafeed?>&calid=<?php echo $_GET["calid"]?>";
        


    });  

</script>  
<?php if (isset($event->status)) { ?>
    <div id="clients_wrapper" style="display:none;">
        <hr>
        <table border="0">
            <tbody id="clients_html">

            </tbody>
        </table>
        <a id="add_client_button" href="javascript:void(0)"><div>Add Client</div></a>
    </div>
<?php }   ?>