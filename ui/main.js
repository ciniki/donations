function ciniki_donations_main() {
	this.init = function() {
		//
		// The main panel
		//
		this.menu = new M.panel('Donation',
			'ciniki_donations_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.donations.main.menu');
		this.menu.year = '';
		this.menu.category = '';
		this.menu.data = {};
		this.menu.sections = {
			'years':{'label':'', 'type':'paneltabs', 'selected':'', 'tabs':{}},
			'categories':{'label':'', 'type':'paneltabs', 'visible':'no', 'selected':'', 'joined':'no', 'tabs':{}},
			'donations':{'label':'', 'type':'simplegrid', 'num_cols':5,
				'sortable':'yes',
				'headerValues':['Receipt #', 'Date', 'Name', 'Amount'],
				'dataMaps':['receipt_number', 'date_received', 'name', 'amount_display'],
				'sortMaps':['receipt_number', 'date_received', 'name', 'amount'],
				'cellClasses':['', '', '', 'alignright'],
				'sortTypes':['number', 'date', 'text', 'altnumber'],
				'noData':'No Donations Found',
				},
//			'_buttons':{'label':'', 'buttons':{
//				'excel':{'label':'Download Excel', 'fn':'M.ciniki_donations_main.downloadExcel(--addyear);'},
//				}},
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
			return 'M.ciniki_donations_main.donationEdit(\'M.ciniki_donations_main.showMenu();\',\'' + d.donation.id + '\');';
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
		this.menu.addButton('add', 'Add', 'M.ciniki_donations_main.donationEdit(\'M.ciniki_donations_main.showMenu();\',0);');
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
				'save':{'label':'Save', 'fn':'M.ciniki_donations_main.donationSave();'},
				'print':{'label':'Print Receipt', 'fn':'M.ciniki_donations_main.donationReceipt();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_donations_main.donationDelete(M.ciniki_donations_main.donation.donation_id);'},
				}},
			};
		this.donation.fieldValue = function(s, i, d) {
			if( this.data != null && this.data[i] != null ) { return this.data[i]; }
			return '';
		};
		this.donation.liveSearchCb = function(s, i, value) {
			if( i == 'category' ) {
				var rsp = M.api.getJSONBgCb('ciniki.donations.donationSearchField', {'business_id':M.curBusinessID, 'field':i, 'start_needle':value, 'limit':15},
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
			return {'method':'ciniki.donations.donationHistory', 'args':{'business_id':M.curBusinessID, 'donation_id':this.donation_id, 'field':i}};
		};
		this.donation.addButton('save', 'Save', 'M.ciniki_donations_main.donationSave();');
		this.donation.addClose('Cancel');
	};

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

		if( M.curBusiness.modules['ciniki.donations'].flags != null
			&& (M.curBusiness.modules['ciniki.donations'].flags&0x01) > 0 ) {
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
		this.showMenu(cb);
	};

	this.showMenu = function(cb, year, category) {
		if( year != null ) { this.menu.year = year; this.menu.sections.years.selected = '_' + year; }
		if( category != null ) { this.menu.category = category; this.menu.sections.categories.selected = category; }
		this.menu.sections.years.tabs = {};
		this.menu.sections.years.visible = 'no';
		this.menu.sections.categories.tabs = {};
		this.menu.sections.categories.visible = 'no';
		M.api.getJSONCb('ciniki.donations.donationList', {'business_id':M.curBusinessID,
			'year':this.menu.year, 'category':this.menu.category}, function(rsp) {
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
						p.sections.years.tabs['_' + years[i]] = {'label':years[i], 'fn':'M.ciniki_donations_main.showMenu(null,\'' + years[i] + '\');'};
					}
				}
				if( (M.curBusiness.modules['ciniki.donations'].flags&0x01) > 0 && rsp.categories != '' ) {
					var categories = rsp.categories.split('::');
					if( categories.length > 1 ) {			// Only show if more than one category
						p.sections.categories.visible = 'yes';
						p.sections.categories.tabs[''] = {'label':'All', 'fn':'M.ciniki_donations_main.showMenu(null,null,\'\');'};
						for(i in categories) {
							p.sections.categories.tabs[categories[i]] = {'label':categories[i], 'fn':'M.ciniki_donations_main.showMenu(null,null,\'' + categories[i] + '\');'};
						}
					}
				}
				p.refresh();
				p.show(cb);
			});
	};

	this.downloadExcel = function() {
		var args = {'business_id':M.curBusinessID, 'output':'excel'};
		if( this.menu.year != null ) { args.year = this.menu.year; }
		M.api.openFile('ciniki.donations.donationList', args);
	};

	this.donationEdit = function(cb, did, cid) {
		if( did != null ) { this.donation.donation_id = did; }
		if( cb != null ) { this.donation.cb = cb; }
		if( this.donation.donation_id > 0 ) {
			//
			// Load the donation
			//
			this.donation.sections._buttons.buttons.delete.visible = 'yes';
			M.api.getJSONCb('ciniki.donations.donationGet', {'business_id':M.curBusinessID,
				'donation_id':this.donation.donation_id}, function (rsp) {
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
			this.donation.reset();
			this.donation.sections._buttons.buttons.delete.visible = 'no';
			this.donation.data = {};
			this.donation.customer_id = cid;
			M.api.getJSONCb('ciniki.donations.donationCustomer', {'business_id':M.curBusinessID,
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
			M.startApp('ciniki.customers.edit',null,this.donation.cb,'mc',{'next':'M.ciniki_donations_main.setCustomer', 'customer_id':0});
		}
	};

	this.setCustomer = function(cid) {
		M.ciniki_donations_main.donationEdit(null, 0, cid);
	};

	this.donationSave = function() {
		if( this.donation.donation_id > 0 ) {
			var c = this.donation.serializeForm('no');
			if( c != '' ) {
				M.api.postJSONCb('ciniki.donations.donationUpdate', {'business_id':M.curBusinessID,
					'donation_id':this.donation.donation_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						M.ciniki_donations_main.donation.close();
					});
			} else {
				this.donation.close();
			}
		} else {
			var c = this.donation.serializeForm('yes');
			M.api.postJSONCb('ciniki.donations.donationAdd', {'business_id':M.curBusinessID,
				'donation_id':this.donation.donation_id, 'customer_id':this.donation.customer_id}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_donations_main.donation.close();
				});
		}
	};

	this.donationDelete = function(did) {
		if( did <= 0 ) { return false; }
		if( confirm("Are you sure you want to remove this donation?") ) {
			M.api.getJSONCb('ciniki.donations.donationDelete', {'business_id':M.curBusinessID,
				'donation_id':did}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_donations_main.donation.close();
				});
		}
	};

	this.donationReceipt = function() {
		if( this.donation.donation_id > 0 ) {
			var args = {'business_id':M.curBusinessID, 'donation_id':this.donation.donation_id, 'output':'pdf'};
			M.api.openPDF('ciniki.donations.donationReceipt', args);
		} else {
			var c = this.donation.serializeForm('yes');
			M.api.postJSONCb('ciniki.donations.donationAdd', {'business_id':M.curBusinessID,
				'donation_id':this.donation.donation_id, 'customer_id':this.donation.customer_id}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_donations_main.donation;
					p.donation_id = rsp.id;
					p.data = rsp.donation;
					var args = {'business_id':M.curBusinessID, 'donation_id':rsp.id, 'output':'pdf'};
					M.api.openPDF('ciniki.donations.donationReceipt', args);
				});
		}
	};
}
