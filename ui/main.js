function ciniki_donations_main() {
    //
    // The main panel
    //
    this.menu = new M.panel('Donation',
        'ciniki_donations_main', 'menu',
        'mc', 'xlarge', 'sectioned', 'ciniki.donations.main.menu');
    this.menu.year = '';
    this.menu.category = '';
    this.menu.data = {};
    this.menu.sections = {
        'years':{'label':'', 'type':'paneltabs', 'selected':'', 'tabs':{}},
        'categories':{'label':'', 'type':'paneltabs', 'visible':'no', 'selected':'', 'joined':'no', 'tabs':{}},
        'donations':{'label':'Donations', 'type':'simplegrid', 'num_cols':5,
            'sortable':'yes',
            'headerValues':['Receipt #', 'Date', 'Name', 'Amount'],
            'dataMaps':['receipt_number', 'date_received', 'name', 'amount_display'],
            'sortMaps':['receipt_number', 'date_received', 'name', 'amount'],
            'cellClasses':['', '', '', '', 'alignright'],
            'sortTypes':['number', 'date', 'text', 'text', 'altnumber'],
            'noData':'No Donations Found',
            'addTxt':'Add',
            'addFn':'M.ciniki_donations_main.donation.open(\'M.ciniki_donations_main.menu.open();\',0);',
            },
//          '_buttons':{'label':'', 'buttons':{
//              'excel':{'label':'Download Excel', 'fn':'M.ciniki_donations_main.menu.downloadExcel(--addyear);'},
//              }},
    };
    this.menu.sectionData = function(s) {
        return this.data[s];
    };
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'donations' ) { return d.donation[this.sections[s].dataMaps[j]]; }
        return '';
    };
    this.menu.cellSortValue = function(s, i, j, d) {
        if( s == 'donations' ) { return d.donation[this.sections[s].sortMaps[j]]; }
    };
    this.menu.rowFn = function(s, i, d) {
        if( d == null ) { return ''; }
        return 'M.ciniki_donations_main.donation.open(\'M.ciniki_donations_main.menu.open();\',\'' + d.donation.id + '\');';
    };
    this.menu.footerValue = function(s, i, d) {
        if( this.data['total_' + this.sections[s].dataMaps[i]] != null ) {
            return this.data['total_' + this.sections[s].dataMaps[i]];
        }
        return '';
    };
    this.menu.footerClass = function(s, i, d) {
        if( i == 3 ) { return 'alignright'; }
        return '';
    };
    this.menu.noData = function(s) {
        return this.sections[s].noData;
    };
    this.menu.open = function(cb, year, category) {
        if( year != null ) { this.year = year; this.sections.years.selected = '_' + year; }
        if( category != null ) { this.category = category; this.sections.categories.selected = category; }
        this.sections.years.tabs = {};
        this.sections.years.visible = 'no';
        this.sections.categories.tabs = {};
        this.sections.categories.visible = 'no';
        M.api.getJSONCb('ciniki.donations.donationList', {'tnid':M.curTenantID,
            'year':this.year, 'category':this.category}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_donations_main.menu;
                p.data = rsp;
                if( rsp.years != '' ) {
                    var years = rsp.years.split('::');
                    p.sections.years.visible = 'yes';
                    for(i in years) {
                        if( p.sections.years.selected == '' ) {
                            p.sections.years.selected = '_' + years[i];
                        }
                        p.sections.years.tabs['_' + years[i]] = {'label':years[i], 'fn':'M.ciniki_donations_main.menu.open(null,\'' + years[i] + '\');'};
                    }
                }
                if( (M.curTenant.modules['ciniki.donations'].flags&0x01) > 0 && rsp.categories != '' ) {
                    var categories = rsp.categories.split('::');
                    if( categories.length > 1 ) {           // Only show if more than one category
                        p.sections.categories.visible = 'yes';
                        p.sections.categories.tabs[''] = {'label':'All', 'fn':'M.ciniki_donations_main.menu.open(null,null,\'\');'};
                        for(i in categories) {
                            p.sections.categories.tabs[categories[i]] = {'label':categories[i], 'fn':'M.ciniki_donations_main.menu.open(null,null,\'' + categories[i] + '\');'};
                        }
                    }
                }
                p.refresh();
                p.show(cb);
            });
    };
    this.menu.downloadExcel = function() {
        var args = {'tnid':M.curTenantID, 'output':'excel'};
        if( this.year != null ) { args.year = this.year; }
        M.api.openFile('ciniki.donations.donationList', args);
    }
    this.menu.addButton('add', 'Add', 'M.ciniki_donations_main.donation.open(\'M.ciniki_donations_main.menu.open();\',0);');
    this.menu.addClose('Back');

    //
    // The add/edit donation panel
    //
    this.donation = new M.panel('Donation',
        'ciniki_donations_main', 'donation',
        'mc', 'medium', 'sectioned', 'ciniki.donations.main.donation');
    this.donation.donation_id = 0;
    this.donation.customer_id = 0;
    this.donation.data = {};
    this.donation.sections = {
        'details1':{'label':'', 'fields':{
            'receipt_number':{'label':'Number', 'type':'text', 'livesearch':'yes'},
            'name':{'label':'Name', 'type':'text', 'livesearch':'yes'},
            'address1':{'label':'Address', 'type':'text'},
            'address2':{'label':'', 'type':'text'},
            'city':{'label':'City', 'type':'text'},
            'province':{'label':'Province', 'type':'text'},
            'postal':{'label':'Postal', 'type':'text'},
            'country':{'label':'Country', 'type':'text', 'size':'small'},
            }},
        'details2':{'label':'', 'fields':{
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'date_received':{'label':'Date Received', 'type':'date', 'size':'small'},
            'amount':{'label':'Amount', 'type':'text', 'size':'small'},
            'date_issued':{'label':'Date Issued', 'type':'date', 'size':'small'},
            'location_issued':{'label':'Location Issued', 'type':'text'},
            }},
        '_notes':{'label':'Notes', 'fields':{
            'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_donations_main.donation.save();'},
            'print':{'label':'Print Receipt', 'fn':'M.ciniki_donations_main.donation.receipt();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_donations_main.donation.remove(M.ciniki_donations_main.donation.donation_id);'},
            }},
        };
    this.donation.fieldValue = function(s, i, d) {
        if( this.data != null && this.data[i] != null ) { return this.data[i]; }
        return '';
    };
    this.donation.liveSearchCb = function(s, i, value) {
        if( i == 'category' ) {
            var rsp = M.api.getJSONBgCb('ciniki.donations.donationSearchField', {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
                function(rsp) {
                    M.ciniki_donations_main.donation.liveSearchShow(s, i, M.gE(M.ciniki_donations_main.donation.panelUID + '_' + i), rsp.results);
                });
        }
    };
    this.donation.liveSearchResultValue = function(s, f, i, j, d) {
        if( f == 'category' && d.result != null ) { return d.result.name; }
        return '';
    };
    this.donation.liveSearchResultRowFn = function(s, f, i, j, d) { 
        if( f == 'category' && d.result != null ) {
            return 'M.ciniki_donations_main.donation.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
        }
    };
    this.donation.updateField = function(s, fid, result) {
        M.gE(this.panelUID + '_' + fid).value = unescape(result);
        this.removeLiveSearch(s, fid);
    };
    this.donation.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.donations.donationHistory', 'args':{'tnid':M.curTenantID, 'donation_id':this.donation_id, 'field':i}};
    };
    this.donation.open = function(cb, did, cid) {
        if( did != null ) { this.donation_id = did; }
        if( cb != null ) { this.cb = cb; }
        if( this.donation_id > 0 ) {
            //
            // Load the donation
            //
            this.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.donations.donationGet', {'tnid':M.curTenantID,
                'donation_id':this.donation_id}, function (rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_donations_main.donation;
                    p.customer_id = rsp.donation.customer_id;
                    p.data = rsp.donation;
                    p.data.amount = p.data.amount_display;
                    p.refresh();
                    p.show();
                });
        } else if( cid != null && cid > 0 ) {
            //
            // Load customer information
            //
            this.reset();
            this.sections._buttons.buttons.delete.visible = 'no';
            this.data = {};
            this.customer_id = cid;
            M.api.getJSONCb('ciniki.donations.donationCustomer', {'tnid':M.curTenantID,
                'customer_id':cid}, function (rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_donations_main.donation;
                    p.data = rsp.donation;
                    p.refresh();
                    p.show();
                });
        } else {
            //
            // Start with customer edit form
            //
            M.startApp('ciniki.customers.edit',null,this.cb,'mc',{'next':'M.ciniki_donations_main.donation.setCustomer', 'customer_id':0});
        }
    }
    this.donation.setCustomer = function(cid) {
        M.ciniki_donations_main.donation.open(null, 0, cid);
    }
    this.donation.save = function() {
        if( this.donation_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.donations.donationUpdate', {'tnid':M.curTenantID,
                    'donation_id':this.donation_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_donations_main.donation.close();
                    });
            } else {
                this.close();
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.donations.donationAdd', {'tnid':M.curTenantID,
                'donation_id':this.donation_id, 'customer_id':this.customer_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_donations_main.donation.close();
                });
        }
    }
    this.donation.remove = function(did) {
        if( did <= 0 ) { return false; }
        if( confirm("Are you sure you want to remove this donation?") ) {
            M.api.getJSONCb('ciniki.donations.donationDelete', {'tnid':M.curTenantID,
                'donation_id':did}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_donations_main.donation.close();
                });
        }
    }
    this.donation.receipt = function() {
        if( this.donation_id > 0 ) {
            var args = {'tnid':M.curTenantID, 'donation_id':this.donation_id, 'output':'pdf'};
            M.api.openPDF('ciniki.donations.donationReceipt', args);
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.donations.donationAdd', {'tnid':M.curTenantID,
                'donation_id':this.donation_id, 'customer_id':this.customer_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_donations_main.donation;
                    p.donation_id = rsp.id;
                    p.data = rsp.donation;
                    var args = {'tnid':M.curTenantID, 'donation_id':rsp.id, 'output':'pdf'};
                    M.api.openPDF('ciniki.donations.donationReceipt', args);
                });
        }
    }
    this.donation.addButton('save', 'Save', 'M.ciniki_donations_main.donation.save();');
    this.donation.addClose('Cancel');

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_donations_main', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        if( M.curTenant.modules['ciniki.donations'].flags != null
            && (M.curTenant.modules['ciniki.donations'].flags&0x01) > 0 ) {
            this.menu.sections.categories.selected = '';
            this.menu.sections.donations.headerValues = ['Receipt #', 'Date', 'Category', 'Name', 'Amount'];
            this.menu.sections.donations.dataMaps = ['receipt_number', 'date_received', 'category', 'name', 'amount_display'];
            this.menu.sections.donations.sortMaps = ['receipt_number', 'date_received', 'category', 'name', 'amount'];
            this.donation.sections.details2.fields.category.active = 'yes';
        } else {
            this.menu.sections.categories.selected = '';
            this.menu.sections.donations.headerValues = ['Receipt #', 'Date', 'Name', 'Amount'];
            this.menu.sections.donations.dataMaps = ['receipt_number', 'date_received', 'name', 'amount_display'];
            this.menu.sections.donations.sortMaps = ['receipt_number', 'date_received', 'name', 'amount'];
            this.donation.sections.details2.fields.category.active = 'no';
        }

        this.menu.year = '';
        this.menu.open(cb);
    }
}
