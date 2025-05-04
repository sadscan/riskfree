$(document).ready(function() {
    $('#calculate').click(function() {
        // Get input values
        const balance = parseFloat($('#balance').val());
        const riskPercent = parseFloat($('#risk-percent').val());
        const stopLoss = parseFloat($('#stop-loss').val());
        const pipValue = parseFloat($('#pair').val());
        const lotSize = parseFloat($('#lot-size').val()) || null;
        
        // Validate inputs
        if (isNaN(balance) || isNaN(riskPercent) || isNaN(stopLoss)) {
            alert('الرجاء إدخال قيم صحيحة لرصيد الحساب ونسبة المخاطرة ونقاط وقف الخسارة');
            return;
        }
        
        // Calculate risk amount
        const riskAmount = (balance * riskPercent) / 100;
        
        // Calculate position size
        let positionSize;
        if (lotSize) {
            positionSize = lotSize;
        } else {
            positionSize = (riskAmount / (stopLoss * pipValue * 10)).toFixed(2);
        }
        
        // Calculate pip value based on position size
        const calculatedPipValue = (positionSize * pipValue * 10).toFixed(2);
        
        // Calculate potential loss
        const potentialLoss = (positionSize * stopLoss * pipValue * 10).toFixed(2);
        
        // Display results
        $('#risk-amount').text('$' + riskAmount.toFixed(2));
        $('#position-size').text(positionSize + ' لوت');
        $('#pip-value').text('$' + calculatedPipValue);
        $('#potential-loss').text('$' + potentialLoss);
    });
    
    // Optional: Set default values or additional functionality
    $('#balance').focus();
});