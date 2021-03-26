<div class="crm-form crm-form-block crm-string_override-form-block">
  <table class="form-layout-compressed" style="width: 100%;">
    <tr>
      <td>
        <table class="ipwarm-summary-table row-highlight">
          <thead>
            <tr class="columnheader">
              <th>{ts}Warmup Age (Days){/ts}</th>
              <th>{ts}Hourly Email Limit{/ts}</th>
              <th>{ts}Daily Email Limit{/ts}</th>
            </tr>
          </thead>
          <tbody class="ipwarm-summary-body">
            {foreach from=$summaryList key=summaryKey item=summary}
              <tr class="ipwarm-summary-row {$summary.class} {cycle values="odd-row,even-row"}">
                <td>{$summaryKey}</td>
                <td>{$summary.hourly}</td>
                <td>{$summary.daily}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </td>
    </tr>
  </table>
</div>
