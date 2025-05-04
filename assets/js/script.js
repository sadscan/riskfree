$(document).ready(function() {
    // Auto-scroll to bottom of table
    $('.table-responsive').scrollTop($('.table-responsive')[0].scrollHeight);
    
    // حساب الربح المستهدف
    function calculateTargetProfit() {
        const initialCapital = parseFloat($('#initial_capital').val()) || 0;
        const totalTrades = parseInt($('#total_trades').val()) || 0;
        const winsNeeded = parseInt($('#wins_needed').val()) || 0;
        const payout = parseFloat($('#payout').val()) || 0;
        
        if (totalTrades > 0 && winsNeeded > 0 && payout >= 1.5) {
            // معادلة مبسطة لحساب الربح المستهدف
            const profit = initialCapital * (Math.pow(payout, winsNeeded) - 1);
            return profit;
        }
        return 0;
    }

    // تحديث معاينة الربح عند تغيير المدخلات
    $('#initial_capital, #total_trades, #wins_needed, #payout').on('input', function() {
        const profit = calculateTargetProfit();
        $('#profitValue').text(profit.toFixed(2));
        
        if (profit < 1 && profit > 0) {
            $('#profitPreview').removeClass('alert-info').addClass('alert-warning');
        } else {
            $('#profitPreview').removeClass('alert-warning').addClass('alert-info');
        }
    });


    // Handle trade form submissions
    $('.trade-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const originalText = button.html();
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    Swal.fire({
                        title: 'خطأ',
                        text: response.error || 'حدث خطأ أثناء المعالجة',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'خطأ في الاتصال',
                    text: 'حدث خطأ في الاتصال بالخادم',
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    });

    // Handle reset form submission
    $('.reset-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const originalText = button.html();
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    Swal.fire({
                        title: 'خطأ',
                        text: response.error || 'حدث خطأ أثناء المعالجة',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'خطأ في الاتصال',
                    text: 'حدث خطأ في الاتصال بالخادم',
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    });

// هذا الكود يجب أن يكون في مكان يضمن تنفيذه في جميع الصفحات
$(document).ready(function() {
    // التحكم في عرض/إخفاء قائمة معلومات الحساب
    $('#toggleUserInfo').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#userInfoContainer').toggleClass('animate__fadeInDown animate__fadeOutUp');
        $('#userInfoContainer').slideToggle(300);
    });

    // إغلاق القائمة عند النقر خارجها
    $(document).click(function(e) {
        if (!$(e.target).closest('#userInfoContainer, #toggleUserInfo').length) {
            $('#userInfoContainer').removeClass('animate__fadeInDown').addClass('animate__fadeOutUp');
            $('#userInfoContainer').slideUp(300);
        }
    });
});
    function confirmLogout() {
        Swal.fire({
            title: 'تأكيد تسجيل الخروج',
            text: 'هل أنت متأكد أنك تريد تسجيل الخروج؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0072ff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، سجل خروج',
            cancelButtonText: 'إلغاء',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    }
});
