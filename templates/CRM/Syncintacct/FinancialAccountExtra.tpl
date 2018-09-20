<table>
<tr id="crm-intacct-class">
  <td class="label">
    {$form.class_id.label}
  </td>
  <td class="html-adjust">
    {$form.class_id.html}<br/>
    <span class="description">{ts}Enter the Intacct Class ID, later used for creating GL/AP entries.{/ts}</span>
  </td>
</tr>
<tr id="crm-intacct-dept">
  <td class="label">
    {$form.dept_id.label}
  </td>
  <td class="html-adjust">
    {$form.dept_id.html}<br/>
    <span class="description">{ts}Enter the Intacct Department ID, later used for creating GL/AP entries.{/ts}</span>
  </td>
</tr>
<tr id="crm-intacct-location">
  <td class="label">
    {$form.location.label}
  </td>
  <td class="html-adjust">
    {$form.location.html}<br/>
    <span class="description">{ts}Enter the Intacct Location, later used for creating GL/AP entries.{/ts}</span>
  </td>
</tr>
<tr id="crm-intacct-project">
  <td class="label">
    {$form.project_id.label}
  </td>
  <td class="html-adjust">
    {$form.project_id.html}<br/>
    <span class="description">{ts}Enter the Intacct Project, later used for creating GL/AP entries.{/ts}</span>
  </td>
</tr>
</table>
{literal}
<script type="text/javascript">
CRM.$(function($) {
  $('.crm-contribution-form-block-accounting_code').after($('#crm-intacct-class'));
  $('#crm-intacct-class').after($('#crm-intacct-dept'));
  $('#crm-intacct-dept').after($('#crm-intacct-location'));
  $('#crm-intacct-location').after($('#crm-intacct-project'));
});
</script>
{/literal}
