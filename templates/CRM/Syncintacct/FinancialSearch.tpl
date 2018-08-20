{literal}
<script type="text/javascript">
CRM.$(function($) {
  $('#Go').click(function() {
   if ($('#batch_update').val() == 'export') {
      var htmlOptions = "{/literal}{$htmlOptions}{literal}";
      $('select.export-format').html(htmlOptions);
      return false;
    }
  });
});
</script>
{/literal}
