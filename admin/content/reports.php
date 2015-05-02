<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 class="inline">
        Reports
    </h1>
    <select id="reptype" onchange="generateReport();" style="vertical-align: middle; margin-right: 20px; margin-bottom: 5px;">
        <option value="stats/takings">Takings Count</option>
        <option value="stats/itemselling">What's Selling</option>
        <option value="stats/supplyselling">Supplier's Selling</option>
        <option value="stats/stock">Current Stock</option>
        <option value="stats/devices">Device Takings</option>
        <option value="stats/locations">Location Takings</option>
        <option value="stats/users">User Takings</option>
        <option value="stats/tax">Tax Breakdown</option>
    </select>
    <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
        <label>Transactions
        <select id="reptranstype" onchange="generateReport();" style="vertical-align: middle; margin-right: 20px; margin-bottom: 5px;">
            <option value="all">All Sales</option>
            <option value="sale">POS Sales</option>
            <option value="invoice">Invoices</option>
        </select>
        </label>
    </div>
    <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
        <label>Range: <input type="text" style="width: 85px;" id="repstime" onclick="$(this).blur();" /></label>
        <label>to <input type="text" style="width: 85px;" id="repetime" onclick="$(this).blur();" /></label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <button onclick="printCurrentReport();" class="btn btn-primary btn-sm"><i class="icon-print align-top bigger-125"></i>Print</button>&nbsp;
        <button class="btn btn-success btn-sm" onclick="exportCurrentReport();"><i class="icon-cloud-download align-top bigger-125"></i>Export CSV</button>
    </div>
</div><!-- /.page-header -->
<div class="row">
    <div class="col-xs-12">
        <!-- PAGE CONTENT BEGINS -->
        <div style="overflow-x: auto; padding: 10px;">
            <div id="reportcontain">

            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<script type="text/javascript">
    var repdata;
    var etime;
    var stime;

    // Generate report
    function generateReport(){
        // show loader
        WPOS.util.showLoader();
        var type = $("#reptype").val();
        // load the data
        repdata = WPOS.sendJsonData(type, JSON.stringify({"stime":stime, "etime":etime, "type":$("#reptranstype").val()}));
        // populate the report using the correct function
        switch (type){
            case "stats/takings":
                populateTakings("Takings Count", "Method");
                break;
            case "stats/itemselling":
                populateSelling(false);
                break;
            case "stats/supplyselling":
                populateSelling(true);
                break;
            case "stats/stock":
                populateStock();
                break;
            case "stats/devices":
                populateTakings("Device Takings", "Device Name");
                break;
            case "stats/locations":
                populateTakings("Location Takings", "Location Name");
                break;
            case "stats/users":
                populateTakings("User Takings", "User Name");
                break;
            case "stats/tax":
                populateTax();
        }
        // hide loader
        WPOS.util.hideLoader();
    }

    // REPORT GEN FUNCTIONS
    function getReportHeader(heading){
        return "<div id='#repheader' style='text-align: center; margin-bottom: 5px;'><h3>"+heading+"</h3><h5>"+$("#repstime").val()+" - "+$("#repetime").val()+"</h5></div>";
    }

    function getCurrentReportHeader(heading){
        var timestamp = new Date();
        timestamp = timestamp.getTime();
        return "<div id='#repheader' style='text-align: center; margin-bottom: 5px;'><h3>"+heading+"</h3><h5>"+WPOS.util.getDateFromTimestamp(timestamp)+"</h5>";
    }

    function populateTakings(repname, colname){
        var html = getReportHeader(repname);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>"+colname+"</td><td># Sales</td><td>Takings</td><td># Refunds</td><td>Refunds</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+(rowdata.hasOwnProperty('name')?rowdata.name:i)+'</a></td><td>'+rowdata.salenum+'</td><td>'+WPOS.currency()+rowdata.saletotal+'</td><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refundrefs+'\');">'+rowdata.refundnum+'</a></td><td>'+WPOS.currency()+rowdata.refundtotal+'</td><td>'+WPOS.currency()+rowdata.balance+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateSelling(issupplier){
        var html = getReportHeader(issupplier?"What Supplier's Selling":"What's Selling");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Sold</td><td>Total</td><td># Refunded</td><td>Total</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.soldqty+'</td><td>'+WPOS.currency()+rowdata.soldtotal+'</td><td>'+rowdata.refundqty+'</td><td>'+WPOS.currency()+rowdata.refundtotal+'</td><td>'+WPOS.currency()+rowdata.balance+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateTax(){
        var html = getReportHeader("Tax Breakdown");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Items</td><td>Sale Total</td><td>Tax</td><td>Refunded</td><td>Refund Tax</td><td>Total Tax</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            if (i!=0){
                rowdata = repdata[i];
                html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.qtyitems+'</td><td>'+WPOS.currency()+rowdata.saletotal+'</td><td>'+WPOS.currency()+rowdata.saletax+'</td><td>'+WPOS.currency()+rowdata.refundtotal+'</td><td>'+WPOS.currency()+rowdata.refundtax+'</td><td>'+WPOS.currency()+rowdata.balance+'</td></tr>';
            }
        }

        html += "</tbody></table><br/>";

        rowdata = repdata[0];
        html += '<p style="text-align: center;">Note: <a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.qty+'</a> sales have been cash rounded to a total amount of '+WPOS.currency()+rowdata.total+'.<br/>Since tax is calculated on a per item level, rounding has not been included in the calculations above.</p>';

        $("#reportcontain").html(html);
    }

    function populateStock(){
        var html = getCurrentReportHeader("Current Stock");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Supplier</td><td>Location</td><td>Stock Qty</td><td>Stock Value</td></tr></thead><tbody>";
        for (var i in repdata){
            rowdata = repdata[i];
            html += "<tr><td>"+rowdata.name+"</td><td>"+rowdata.supplier+"</td><td>"+rowdata.location+"</td><td>"+rowdata.stocklevel+"</td><td>"+rowdata.stockvalue+"</td></tr>"
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function printCurrentReport(){
        browserPrintHtml($("#reportcontain").html());
    }

    function exportCurrentReport(){
        var data  = WPOS.table2CSV($("#reportcontain"));
        var filename = $("#reportcontain div h3").text()+"-"+$("#reportcontain div h5").text();
        filename = filename.replace(" ", "");
        WPOS.initSave(filename, data);
    }

    function browserPrintHtml(html){
        var printw = window.open('', 'wpos report', 'height=800,width=650');
        printw.document.write('<html><head><title>Wpos Report</title>');
        printw.document.write('<link media="all" href="assets/css/bootstrap.min.css" rel="stylesheet"/><link media="all" rel="stylesheet" href="assets/css/font-awesome.min.css"/><link media="all" rel="stylesheet" href="assets/css/ace-fonts.css"/><link media="all" rel="stylesheet" href="assets/css/ace.min.css"/>');
        printw.document.write('</head><body style="background-color: #FFFFFF;">');
        printw.document.write(html);
        printw.document.write('</body></html>');
        printw.document.close();

        printw.print();
        printw.close();
    }

    $(function(){
        etime = new Date().getTime();
        stime = (etime - 604800000); // a week ago

        $("#repstime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(etime),
            onSelect: function(text, inst){
                var date = $("#repstime").datepicker("getDate");
                date.setHours(0); date.setMinutes(0); date.setSeconds(0);
                stime = date.getTime();
                generateReport();
            }
        });
        $("#repetime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(etime),
            onSelect: function(text, inst){
                var date = $("#repetime").datepicker("getDate");
                date.setHours(23); date.setMinutes(59); date.setSeconds(59);
                etime = date.getTime();
                generateReport();
            }
        });

        $("#repstime").datepicker('setDate', new Date(stime));
        $("#repetime").datepicker('setDate', new Date(etime));
        generateReport(); // generate initial report

        // hide loader
        WPOS.util.hideLoader();
    });
</script>