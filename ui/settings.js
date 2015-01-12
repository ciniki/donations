//
function ciniki_donations_settings() {
	this.toggleOptions = {'no':'Hide', 'yes':'Display'};
	this.positionOptions = {'left':'Left', 'center':'Center', 'right':'Right', 'off':'Off'};

	this.init = function() {
		//
		// The donation settings panel
		//
		this.main = new M.panel('Settings',
			'ciniki_donations_settings', 'main',
			'mc', 'medium', 'sectioned', 'ciniki.donations.settings.main');
		this.main.sections = {
			'image':{'label':'Header Image', 'fields':{
				'receipt-header-image':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'header':{'label':'Header Address Options', 'fields':{
				'receipt-header-contact-position':{'label':'Position', 'type':'toggle', 'default':'center', 'toggles':this.positionOptions},
				'receipt-header-business-name':{'label':'Business Name', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
				'receipt-header-business-address':{'label':'Address', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
//				'receipt-header-business-phone':{'label':'Phone', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
//				'receipt-header-business-cell':{'label':'Cell', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
//				'receipt-header-business-fax':{'label':'Fax', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
//				'receipt-header-business-email':{'label':'Email', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
//				'receipt-header-business-website':{'label':'Website', 'type':'toggle', 'default':'yes', 'toggles':this.toggleOptions},
				}},
			'_charity_info':{'label':'', 'fields':{
				'receipt-signing-officer':{'label':'Signing Officer', 'type':'text'},
				'receipt-charity-number':{'label':'Charity Number', 'type':'text'},
				'default-location-issued':{'label':'Location Issued', 'type':'text'},
				}},
			'_thank_you_msg':{'label':'Thank You Message', 'fields':{
				'receipt-thankyou-message':{'label':'', 'hidelabel':'yes', 'type':'text'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_donations_settings.saveSettings();'},
				}},
		};
		this.main.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.donations.settingsHistory', 
				'args':{'business_id':M.curBusinessID, 'setting':i}};
		}
		this.main.fieldValue = function(s, i, d) {
			if( this.data[i] == null && d.default != null ) { return d.default; }
			return this.data[i];
		};
		this.main.addDropImage = function(iid) {
			M.ciniki_donations_settings.main.setFieldValue('receipt-header-image', iid);
			return true;
		};
		this.main.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.main.addButton('save', 'Save', 'M.ciniki_donations_settings.saveSettings();');
		this.main.addClose('Cancel');
	}

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
		var appContainer = M.createContainer(appPrefix, 'ciniki_donations_settings', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.showSettings(cb);
	}

	//
	// show the invoice settings
	//
	this.showSettings = function(cb) {
		M.api.getJSONCb('ciniki.donations.settingsGet', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_donations_settings.main;
			p.data = rsp.settings;
			p.refresh();
			p.show(cb);
		});
	};

	//
	// Save the Invoice settings
	//
	this.saveSettings = function() {
		var c = this.main.serializeForm('no');
		if( c != '' ) {
			M.api.postJSONCb('ciniki.donations.settingsUpdate', {'business_id':M.curBusinessID}, 
				c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_donations_settings.main.close();
				});
		} else {
			this.main.close();
		}
	};
}
