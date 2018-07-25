
<script src="https://unpkg.com/bpmn-js@2.4.0/dist/bpmn-viewer.development.js"></script>

<div id="canvas" style="position: relative;height: 350px">
    <div class="entry" style="position: absolute;bottom: 0px;right: 0px;z-index: 200;background: white;">
        <a href="{{ url('/bpmn/index.html?id=').$model->id }}" target="_blank" ><i class="fa fa-edit fa-5x"></i></a>
    </div>
</div>

<script>

//    var diagramUrl = 'diagram.bpmn';

    // viewer instance
    var bpmnViewer = new BpmnJS({
        container: '#canvas'
    });


    /**
     * Open diagram in our viewer instance.
     *
     * @param {String} bpmnXML diagram to display
     */
    function openDiagram(bpmnXML) {

        // import diagram
        bpmnViewer.importXML(bpmnXML, function(err) {

        if (err) {
            return console.error('could not import BPMN 2.0 diagram', err);
        }

        // access viewer components
        var canvas = bpmnViewer.get('canvas');
        var overlays = bpmnViewer.get('overlays');


        // zoom to fit full viewport
        canvas.zoom('fit-viewport');

        // attach an overlay to a node
        overlays.add('SCAN_OK', 'note', {
            position: {
                bottom: 0,
                right: 0
            },
            html: '<div class="diagram-note">Mixed up the labels?</div>'
        });

        // add marker
        canvas.addMarker('SCAN_OK', 'needs-discussion');
    });
    }

    openDiagram(decodeURIComponent('{{ $bpmnXML }}'));
    // load external diagram file via AJAX and open it
//    $.get(diagramUrl, openDiagram, 'text');
</script>