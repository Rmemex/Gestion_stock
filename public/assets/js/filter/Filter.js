$(document).ready(function(){
    $(document).on('change','#select_date', function(e){
        e.preventDefault();
        const url = e.target.getAttribute('params-date');
        
        let date = $(this).val();

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                date: date,
            },
            success: function (response) {
                $('.js-table').html(response.table);
            },
            error: function (error) {
                console.log(error);
            }
        })
    });

    $(document).on('change','#select_frn', function(e){
        e.preventDefault();
        const url = e.target.getAttribute('params-frn');
        
        let fournisseur = $(this).val();

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                fournisseur: fournisseur,
            },
            success: function (response) {
                $('.js-table').html(response.table);
            },
            error: function (error) {
                console.log(error);
            }
        })
    });
})