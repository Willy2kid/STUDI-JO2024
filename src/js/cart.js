
$('.update-cart-button').on('click', function() {
    event.preventDefault();
    console.log('Bouton cliqué');
    
    var form = $(this).closest('form');
    var formData = form.serialize();
    var submitButton = $(this).data('value');

    formData += '&offer=' + submitButton;

    $.ajax({
        type: 'POST',
        url: '/update-cart',
        data: formData,
        dataType: 'json',
        success: function(data) {
            // Mettez à jour le contenu du panier avec les données reçues
            updateCartContent(data);
        },
        error: function(xhr, status, error) {
            console.error('Erreur:', error);
        }
    });
});

function updateCartContent(cartData) {
    // Sélectionnez l'élément du DOM où vous souhaitez afficher le contenu du panier
    const cartElement = $('#cartContent');
    
    // Videz le contenu existant
    cartElement.empty();
    
    // Construisez le nouveau contenu du panier en fonction des données reçues
    const card = $('<div>').addClass('card mt-4 mt-md-0');
    const cardHeader = $('<h5>').addClass('card-header bg-dark text-white').text('Résumé');
    card.append(cardHeader);
    
    const listGroup = $('<ul>').addClass('list-group list-group-flush');
    $.each(cartData.items, function(index, item) {
        const itemElement = $('<li>').addClass('list-group-item py-1').css('border', 'none');
        const itemText = $('<div>').text(`${item.name} (offre ${item.offer})`);
        itemElement.append(itemText);
        listGroup.append(itemElement);
    });
    
    const totalElement = $('<li>').addClass('list-group-item d-flex justify-content-between').css('border-top', '1px solid #ddd');
    const totalText = $('<div>').html('<b>Total</b>');
    const totalValue = $('<span>').html(`<b>${cartData.total} €</b>`);
    totalElement.append(totalText).append(totalValue);
    listGroup.append(totalElement);
    
    card.append(listGroup);
    
    const cardBody = $('<div>').addClass('card-body');
    const voirAchatsButton = $('<a>').addClass('btn btn-warning w-100').attr('href', '/cart').text('Voir vos achats');
    cardBody.append(voirAchatsButton);
    card.append(cardBody);
    
    cartElement.append(card);
}