function ciniki_donations_main() {
	this.init = function() {
		//
		// The main panel
		//
		this.menu = new M.panel('Donation',
			'ciniki_donations_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.donations.main.menu');
		this.menu.year = '';
		this.menu.data = {};
		this.menu.sections = {
			'years':{'label':'', 'type':'paneltabs', 'selected':'', 'tabs':{}},
			'donations':{'label':'', 'type':'simplegrid', 'num_cols':5,
				'sortable':'yes',
				'headerValues':['Receipt #', 'Date', 'Name', 'Amount'],
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
			if( s == 'donations' ) {
				switch(j) {
					case 0: return d.donation.receipt_number;
					case 1: return d.donation.date_received;
					case 2: return d.donation.name;
					case 3: return d.donation.amount_display;
				}
			}
			return '';
		};
		this.menu.cellSortValue = function(s, i, j, d) {
			switch(j) {
				case 0: return d.donation.receipt_number;
				case 1: return d.donation.date_received;
				case 2: return d.donation.name;
				case 3: return d.donation.amount;
			}
		};
		this.menu.rowFn = function(s, i, d) {
			return 'M.ciniki_donations_main.donationEdit(\'M.ciniki_donations_main.showMenu();\',\'' + d.donation.id + '\');';
		};
		this.menu.footerValue = function(s, i, d) {
			if( this.data.totals != null ) {
				switch(i) {
					case 0: return '';
					case 1: return '';
					case 2: return '';
					case 3: return this.data.total_amount_display;
				}
			}
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
		this.donation.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.donations.donationHistory', 'args':{'business_id':M.curBusinessID, 'field':i}};
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

		this.menu.year = '';
		this.showMenu(cb);
	};

	this.showMenu = function(cb, year) {
		if( year != null ) { this.menu.year = year; this.menu.sections.years.selected = year; }
		this.menu.sections.years.tabs = {};
		this.menu.sections.years.visible = 'no';
		M.api.getJSONCb('ciniki.donations.donationList', {'business_id':M.curBusinessID,
			'year':this.menu.year}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_donations_main.menu;
				p.data = rsp;
				if( rsp.years != '' ) {
					var years = rsp.years.split(',');
					p.sections.years.visible = 'yes';
					for(i in years) {
						if( p.sections.years.selected == '' ) {
							p.sections.years.selected = years[i];
						}
						p.sections.years.tabs[years[i]] = {'label':years[i], 'fn':'M.ciniki_donations_main.showMenu(null,\'' + years[i] + '\');'};
					}
				}
				p.refresh();
				p.show(cb);
			});
	};

	this.downloadExcel = function() {
		var args = {'business_id':M.curBusinessID, 'output':'excel'};
		if( this.menu.year != null ) { args.year = this.menu.year; }
		window.open(M.api.getUploadURL('ciniki.donations.donationList', args));
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
			window.open(M.api.getUploadURL('ciniki.donations.donationReceipt', args));
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
					window.open(M.api.getUploadURL('ciniki.donations.donationReceipt', args));
				});
		}
	};
}
