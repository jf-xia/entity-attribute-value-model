<?php

namespace Eav\Admin\Extensions;

use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Validator;

class Bpmn extends Field
{

    protected $view = 'eav::admin.form.bpmn';

    /**
     * Css.
     *
     * @var array
     */
    protected static $css = [
        '/bpmn/dist/assets/diagram-js.css?v=1',
        '/bpmn/dist/assets/bpmn-font/css/bpmn-embedded.css?v=1',
//        '/bpmn/css/app.css?v=1',
    ];

    /**
     * Js.
     *
     * @var array
     */
    protected static $js = [
//        '/bpmn/dist/bpmn-modeler.development.js?v=1',
//        '/bpmn/custom-viewer.bundled.js?v=1',
        '/bpmn/index.js?v=1',
    ];

    /**
     * Render file upload field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $name = $this->formatName($this->column);
        $newDiagramXML = '<?xml version="1.0" encoding="UTF-8"?><bpmn2:definitions xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:bpmn2="http://www.omg.org/spec/BPMN/20100524/MODEL" xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI" xmlns:dc="http://www.omg.org/spec/DD/20100524/DC" xmlns:di="http://www.omg.org/spec/DD/20100524/DI" xsi:schemaLocation="http://www.omg.org/spec/BPMN/20100524/MODEL BPMN20.xsd" id="sample-diagram" targetNamespace="http://bpmn.io/schema/bpmn">  <bpmn2:process id="Process_1" isExecutable="false">    <bpmn2:startEvent id="StartEvent_1"/>  </bpmn2:process>  <bpmndi:BPMNDiagram id="BPMNDiagram_1">    <bpmndi:BPMNPlane id="BPMNPlane_1" bpmnElement="Process_1">      <bpmndi:BPMNShape id="_BPMNShape_StartEvent_2" bpmnElement="StartEvent_1">        <dc:Bounds height="36.0" width="36.0" x="412.0" y="240.0"/>      </bpmndi:BPMNShape>    </bpmndi:BPMNPlane>  </bpmndi:BPMNDiagram></bpmn2:definitions>';
        $this->script = <<<EOT
var diagramXML=decodeURIComponent('{$this->value}');
if(!diagramXML){
  diagramXML='{$newDiagramXML}';
}
var container = $('#js-drop-zone');
var modeler = new BpmnJS({
  container: '#js-canvas',
  propertiesPanel: {
    parent: '#js-properties-panel'
  },
//  additionalModules: [
//    this.propertiesPanelModule,
//    this.propertiesProviderModule
//  ],
//  moddleExtensions: {
//    camunda: this.camundaModdleDescriptor
//  }
});

openDiagram(diagramXML);

function openDiagram(xml) {
  modeler.importXML(xml, function(err) {
    if (err) {
      container.find('.error').text(err.message);
      console.error(err);
    }
  });
}

// file save handling
var download = $('[data-download]');
function serialize() {
  modeler.saveXML(function(err, xml) {
    var encodedData = err ? '' : encodeURIComponent(xml);
    $('#{$name}').val(encodedData);
    download.attr({
      'href': encodedData ? 'data:application/bpmn20-xml;charset=UTF-8,' + encodedData : '',
    });
    if (err) {
      console.log('failed to serialize BPMN 2.0 xml', err);
    }
  });
}
modeler.on('comments.updated', serialize);
modeler.on('commandStack.changed', serialize);
modeler.on('canvas.click', function() {
  modeler.get('comments').collapseAll();
});

// file open handling
var file = $('[data-open-file]');
function readFile(file, done) {
  if (!file) {
    return done(new Error('no file chosen'));
  }
  var reader = new FileReader();
  reader.onload = function(e) {
    done(null, e.target.result);
  };
  reader.readAsText(file);
}
file.on('change', function() {
  readFile(this.files[0], function(err, xml) {
    if (err) {
      alert('could not read file, see console');
      return console.error('could not read file', err);
    }
    openDiagram(xml);
  });
});

EOT;
        return parent::render();
    }
}
