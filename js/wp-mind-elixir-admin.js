jQuery(document).ready(function($){
    let mind;
    // Initialize Mind Elixir (with saved data or a new map).
    if ( MEMapData.initial ) {
        try {
            const initialData = JSON.parse(MEMapData.initial);
            mind = new MindElixir({ el: '#map' });
            mind.init(initialData);
        } catch (e) {
            console.error('Failed to parse saved map data:', e);
            mind = new MindElixir({ el: '#map' });
            mind.init(MindElixir.new('New Mind Map'));
        }
    } else {
        mind = new MindElixir({ el: '#map' });
        mind.init(MindElixir.new('New Mind Map'));
    }

    // Save button handler: send data via AJAX to PHP.
    $('#save-map-button').on('click', function(){
        const data = mind.getData();  // Get current map data object.
        const dataString = JSON.stringify(data);
        $.post(MEMapData.ajax_url, {
            action: 'save_mind_map',
            data: dataString,
            nonce: MEMapData.nonce
        })
        .done(function(response){
            if (response.success) {
                $('#save-status').text('Mind map saved successfully!');
            } else {
                $('#save-status').text('Error saving mind map.');
            }
        })
        .fail(function(){
            $('#save-status').text('AJAX error.');
        });
    });

    // Reset button handler: create a new root node and refresh the map.
    $('#reset-map-button').on('click', function(){
        const newData = MindElixir.new('New Mind Map');
        mind.refresh(newData);  // Replace with a new mind map.
        $('#save-status').text('');
    });
});
