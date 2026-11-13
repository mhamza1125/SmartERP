{{--
    Outstanding loans of the selected payee on the Pay Employee / Vendor / Contractor forms.
    $party: employee | vendor | contractor; $amountField: name of the amount input;
    $excludeId: transaction being edited (left out of the totals).
    Loans come from the Give Loan / Receive Loan entries (payroll deductions included).
--}}
<div class="card border mb-3" id="loan_summary" style="display:none;">
  <div class="card-body py-2">
    <h6 class="mb-2"><i class="fas fa-hand-holding-usd text-warning"></i> Loans of this {{ $party }}</h6>
    <div class="table-responsive">
      <table class="table table-sm mb-1">
        <thead>
          <tr>
            <th>Loan</th>
            <th class="text-right">Total Given</th>
            <th class="text-right">Repaid</th>
            <th class="text-right">Outstanding</th>
          </tr>
        </thead>
        <tbody id="loan_summary_rows"></tbody>
      </table>
    </div>
    <div id="loan_repayment_ratio" class="small" style="display:none;"></div>
  </div>
</div>

<script>
  $(document).ready(function () {
    var loanUrl = "{{ route('ajaxLoan') }}";
    var party = @json($party);
    var excludeId = @json($excludeId ?? null);
    var $amount = $('input[name="{{ $amountField }}"]');
    var $type = $('select[name="transaction_type"]');
    var $advanceType = $('select[name="advance_type"]');
    var labels = { fixed: 'Fixed Loan', temporary: 'Temporary Advance', loan: 'Loan' };
    var loans = {};
    var money = function (value) { return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    // Which loan a Receive Loan entry repays: employees choose the loan type
    function currentKind() {
      return party === 'employee' ? ($advanceType.val() || 'fixed') : 'loan';
    }

    // Repayment as a share of the outstanding loan, and what is left after it
    function showRatio() {
      var $ratio = $('#loan_repayment_ratio');
      var line = loans[currentKind()];
      if ($type.val() !== 'receiveAdvance' || !line) { $ratio.hide(); return; }
      var amount = parseFloat($amount.val()) || 0;
      var outstanding = parseFloat(line.outstanding) || 0;
      if (outstanding <= 0) {
        $ratio.html('<span class="text-danger">No outstanding ' + labels[currentKind()].toLowerCase() + ' to receive.</span>').show();
        return;
      }
      var percent = (amount / outstanding * 100);
      var over = amount > outstanding + 0.001;
      $ratio.html(
        '<strong>Deduction:</strong> ' + money(amount) + ' = <strong>' + percent.toFixed(1) + '%</strong> of the outstanding ' +
        labels[currentKind()].toLowerCase() + ' (' + money(outstanding) + '). Remaining after this: <strong>' +
        money(Math.max(0, outstanding - amount)) + '</strong>' +
        (over ? ' <span class="text-danger">- more than is outstanding</span>' : '')
      ).toggleClass('text-danger', over).show();
    }

    function render() {
      var rows = '';
      var any = false;
      $.each(loans, function (kind, line) {
        if (line.given > 0 || line.repaid > 0) { any = true; }
        rows += '<tr><td>' + labels[kind] + '</td><td class="text-right">' + money(line.given) + '</td><td class="text-right">' +
          money(line.repaid) + '</td><td class="text-right font-weight-bold ' + (line.outstanding > 0 ? 'text-danger' : '') + '">' +
          money(line.outstanding) + '</td></tr>';
      });
      $('#loan_summary_rows').html(rows);
      // Shown when the payee has any loan history, or when a loan is being given / received
      $('#loan_summary').toggle(any || ['advance', 'receiveAdvance'].indexOf($type.val()) !== -1);
      showRatio();
    }

    function load() {
      var partyId = $('#payee_id').val();
      if (!partyId) { loans = {}; $('#loan_summary').hide(); return; }
      $.getJSON(loanUrl, { party: party, partyId: partyId, exclude: excludeId }, function (response) {
        loans = response.loans || {};
        render();
      });
    }

    $('#payee_id').on('change', load);
    $type.on('change', render);
    $advanceType.on('change', showRatio);
    $amount.on('input change', showRatio);
    load();
  });
</script>
