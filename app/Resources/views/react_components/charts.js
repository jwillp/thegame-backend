var ProgressionDoughnut = React.createClass({

    componentDidMount : function() {

        var canvasId = this.props.uid;
        var progression = this.props.progression;


        var completedColor = "#FFCE56";
        // Choose color depending on progression
        if(progression == 100)
            completedColor = "#5cb85c";
        if(progression <= 20)
            completedColor = "#d9534f";
        else if(progression >= 75)
            completedColor = "#5bc0de";

        //var todoColor = "#2b3e50"; // superhero
        var todoColor = "#1c1e22";
        var cutOut = 70; /* percentage */

        var todoProgression = 100 - progression;

        var ctx = document.getElementById(canvasId).getContext("2d");
        var chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [
                    {
                        data: [progression, todoProgression],
                        backgroundColor: [
                            completedColor,
                            todoColor
                        ],
                        hoverBackgroundColor: [
                            completedColor,
                            todoColor
                        ],
                        borderWidth : 0
                    }
                ]
            },
            options: {
                cutoutPercentage: cutOut,
                responsive: false,
                animation: {
                    onComplete : function () {  
                        var canvasWidthvar = $('#'+canvasId).width();
                        var canvasHeight = $('#'+canvasId).height();
                        var constant = 100;
                        var fontsize = (canvasHeight/constant).toFixed(2);
                        //ctx.font="2.8em Verdana";
                        ctx.font=fontsize +"em Verdana";
                        ctx.textBaseline="middle"; 
                        var tpercentage = ((progression/100)*100).toFixed(0)+"%";
                        var textWidth = ctx.measureText(tpercentage).width;

                        var txtPosx = Math.round((canvasWidthvar - textWidth)/2);
                        ctx.fillStyle = 'white';
                        ctx.fillText(tpercentage, txtPosx, canvasHeight/2);
                    }    
               },
               tooltips: {
                enabled: false
               },
               hover: {
                    onHover : function () {
                        // body...
                    }
               }
            }
        });
    },

    render: function() {
        return (
            <canvas id={this.props.uid} width="160" height="160"/>
        );
    }
});