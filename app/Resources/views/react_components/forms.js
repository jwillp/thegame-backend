/**
* Simple Bootstrap + React forms
*/

// A Form Input Field
// Text input, textarea, drop down etc ...
var FormInputField = React.createClass({
  getInitialState: function() {
    return {
      value: ''
    }
  },

  handleChange : function(event) {
    this.setState({value : event.target.value },
      () => {this.props.schema.value = this.state.value } // for async so last
                                                          // chatracter is taken
                                                          // into account
     );
  },

  render: function() {
      var schema = this.props.schema;
      var title = schema.label;
      var lowerCaseTitle = title.toLowerCase();

      var field;
      if (schema.type == 'text'){
        field = this.renderTextInput();
      }else if (schema.type == 'textarea'){
        field = this.renderTextArea();
      }else{
        field = (<p>INVALID FIELD TYPE</p>);
      };

      return (
        <div className="form-group">
          <label className="col-md-4 control-label" for={lowerCaseTitle}>{title}</label>
          <div className="col-md-4">
            {field}
          </div>
        </div>
      );
  },

  renderTextInput: function(){
    var schema = this.props.schema;
    var title = schema.label;
    var lowerCaseTitle = title.toLowerCase();
    return (
      <input id={lowerCaseTitle}
             onChange={this.handleChange}
             name={lowerCaseTitle}
             type={schema.type}
             placeholder={schema.placeholder || null}
             className="form-control input-md"
             required={schema.required || null} value={this.state.value} />
    );
  },

  renderTextArea: function() {
     var schema = this.props.schema;
     var title = schema.label;
     var lowerCaseTitle = title.toLowerCase();
     return (
      <textarea id={lowerCaseTitle}
              onChange={this.handleChange}
              name={lowerCaseTitle}
              type="textarea"
              placeholder={schema.placeholder || null}
              className="form-control input-md"
              required={schema.required || null} value={this.state.value} />
    );
  },
});



// Some kind of event listener to tie a form to
// a trigger submit button, that do not share a parent-child
// relationship.
function FormListener() {
  return {  notifySubmitEvent : function () {
      // This function should be redefined as such:
      // (in Form.componentDidMount)
      // this.props.listener.notifySubmitEvent = this.onSubmit;
      // where this.onSubmit is a method.
      // (in Trigger) onClick="this.props.listener.notifySubmitEvent()"
    }
  }
}


// A Form component.
// The form is generated using a
// form schema:
//
// var challengeSchema = {
//    title:       {type : "text",     label : "Title",       defaultValue : ""},
//    description: {type : "textarea", label : "Description", defaultValue : ""},
//    nbPoints:    {type : "text",     label : "nbPoints",    defaultValue : "10"}
//  };
//
var Form = React.createClass({

  schemaToValues: function (){
    var values = {}
    var schema = this.props.schema;
    Object.keys(schema).map(function(fieldName) {
      var field = schema[fieldName];
      values[fieldName] = field.value;
    });
    return values;
  },

  componentDidMount : function() {
    this.props.listener.notifySubmitEvent = this.onSubmit;
  },

  onSubmit: function(e){
    var url = this.props.url;
    // Generate data from updated schema
    var params = this.props.urlParameters || {};
    var values = this.schemaToValues();
    //console.debug(values);
    params.data = values;
    //console.debug(params.data);
    var id = this.props.id;
    $.ajax({
        url: url,
        data: params,
      })
      .done(function(data) {
          console.log("success");
          if (data.status != "OK"){
            console.debug(data.status)
            bootbox.alert('There was an error : ' + data.errorReport);
          }else{
            Lite.EventManager.fireEvent('FormOKEvent', id);
          }
      })
      .fail(function(xhr, status, err) {
          console.error(xhr, status, err.toString());
          bootbox.alert('There was an error :  ' + err.toString());
          // TODO put fields in red and display error message
      })
      .always(function() {
          console.log("complete");
    });
  },
  render: function() {
    var schema = this.props.schema;
    var nodes = Object.keys(schema).map(function(fieldName) {
      var field = schema[fieldName];
      return (<FormInputField key={fieldName} schema={field} />);
    });
    return (
      <form className="form-horizontal">
        <fieldset>
          {nodes}
        </fieldset>
      </form>
    );
  }
});

// Class responsible for sending the event
// the the user submited the form
var FormSubmitTrigger = React.createClass({
  handleClick : function(){
    // Notify form using listener
    this.props.listener.notifySubmitEvent();
  },

  render: function() {
    return (
      <a className={"btn btn-" + this.props.type} onClick={this.handleClick}>{this.props.text}</a>
    );
  }
});