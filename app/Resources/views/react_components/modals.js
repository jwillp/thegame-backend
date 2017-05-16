/**
* Easy Bootstrap modal integration
*/



// Header of a modal
var ModalHeader = React.createClass({
  render: function() {
    return (
      <div className="modal-header">
        <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 className="modal-title">{this.props.children}</h4>
      </div>
    );
  }
});

// Body of a modal
var ModalBody = React.createClass({

  render: function() {
    return (
      <div className="modal-body">
        {this.props.children}
      </div>
    );
  }
});

// Footer of a modal
var ModalFooter = React.createClass({
  render: function() {
    return (
      <div className="modal-footer">
        {this.props.children}
      </div>
    );
  }
});
/*<button type="button" className="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" className="btn btn-primary">Save changes</button>*/


// A generic modal
var Modal = React.createClass({
  componentDidMount(){
      $(ReactDOM.findDOMNode(this)).modal('show');
      $(ReactDOM.findDOMNode(this)).on('hidden.bs.modal', this.props.modalController.hideModal);
  },
  render(){
      return (
        <div className="modal fade">
          <div className="modal-dialog">
            <div className="modal-content">
              {this.props.children}
            </div>
          </div>
        </div>
      )
  },
  propTypes:{
     /* modalController: React.PropTypes.object.isRequired*/
  }
});




var ModalForm = Lite.createClass({

  formListener : FormListener(),

  onComponentMounted(){
      $(ReactDOM.findDOMNode(this)).modal('show');
      $(ReactDOM.findDOMNode(this)).on('hidden.bs.modal', this.props.modalController.hideModal);
  },

  onEvent: {
        onFormOKEvent: function(emitter, formId) {
          if(formId == this.self.props.formId)
            $("#"+formId).modal('hide');
        },
  },

  render: function() {

    var submitUrl = this.props.submitUrl;
    var submitUrlParams = this.props.submitUrlParams;
    var formSchema = this.props.formSchema;
    var formId = this.props.formId;
    var title = this.props.title;
    var formListener = this.formListener;

    return (
        <div className="modal fade" id={formId}>
          <div className="modal-dialog">
            <div className="modal-content">
              {this.props.children}
              <ModalHeader>{title}</ModalHeader>
              <ModalBody>
                <Form url={submitUrl}
                      urlParameters={submitUrlParams}
                      id={formId}
                      schema={formSchema}
                      listener={formListener} />
              </ModalBody>
              <ModalFooter>
                <FormSubmitTrigger type="info" text="Create" listener={formListener} data-dismiss="modal"/>
                <button type="button" className="btn btn-default" data-dismiss="modal">Close</button>
              </ModalFooter>
             </div>
          </div>
        </div>
    );
  }

});


/**
* Button used to trigger a modal
*/
var ModalTrigger = React.createClass({
      getInitialState(){
          return {view: {showModal: false}}
      },

      hideModal: function(){
          this.setState({view: {showModal: false}})
      },
      showModal: function(){
          this.setState({view: {showModal: true}})
      },

      propagateToChildren: function(children) {
        const childrenWithProps = React.Children.map(children,
         (child) => React.cloneElement(child, {
           modalController: this
         })
        );
        return childrenWithProps
      },

      render(){
        var childrenWithProps = this.propagateToChildren(this.props.children);
        return(
          <div>
            <p>
              <button className={"btn btn-"+this.props.type} onClick={this.showModal}>{this.props.text}</button>
            </p>
              {this.state.view.showModal ? childrenWithProps : null}
          </div>
        );
    }
  });