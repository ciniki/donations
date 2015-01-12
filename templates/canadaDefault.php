<?php
//
// Description
// ===========
// This method will produce a PDF receipt for a donation.
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_donations_templates_canadaDefault(&$ciniki, $business_id, $donation_id, $business_details, $settings) {
	//
	// Get the receipt record
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'donationLoad');
	$rc = ciniki_donations_donationLoad($ciniki, $business_id, $donation_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$donation = $rc['donation'];
	
	//
	// Load TCPDF library
	//
	require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

	class MYPDF extends TCPDF {
		//Page header
		public $header_image = null;
		public $header_name = '';
		public $header_addr = array();
		public $header_details = array();
		public $header_height = 0;		// The height of the image and address
		public $business_details = array();
		public $settings = array();

		public function Header() {
			//
			// Check if there is an image to be output in the header.   The image
			// will be displayed in a narrow box if the contact information is to
			// be displayed as well.  Otherwise, image is scaled to be 100% page width
			// but only to a maximum height of the header_height (set far below).
			//
			$img_width = 0;
			if( $this->header_image != null ) {
				$height = $this->header_image->getImageHeight();
				$width = $this->header_image->getImageWidth();
				$image_ratio = $width/$height;
				if( count($this->header_addr) == 0 && $this->header_name == '' ) {
					$img_width = 180;
				} else {
					$img_width = 120;
				}
				$available_ratio = $img_width/$this->header_height;
				// Check if the ratio of the image will make it too large for the height,
				// and scaled based on either height or width.
				error_log($this->getY());
				if( $available_ratio < $image_ratio ) {
					$this->Image('@'.$this->header_image->getImageBlob(), 15, $this->getY()+7, 
						$img_width, 0, 'JPEG', '', 'L', 2, '150');
				} else {
					$this->Image('@'.$this->header_image->getImageBlob(), 15, $this->getY()+7, 
						0, $this->header_height-5, 'JPEG', '', 'L', 2, '150');
				}
			}

			//
			// Add the contact information
			//
			if( !isset($this->settings['receipt-header-contact-position']) 
				|| $this->settings['receipt-header-contact-position'] != 'off' ) {
				if( isset($this->settings['receipt-header-contact-position'])
					&& $this->settings['receipt-header-contact-position'] == 'left' ) {
					$align = 'L';
				} elseif( isset($this->settings['receipt-header-contact-position'])
					&& $this->settings['receipt-header-contact-position'] == 'right' ) {
					$align = 'R';
				} else {
					$align = 'C';
				}
				$this->Ln(8);
				if( $this->header_name != '' ) {
					$this->SetFont('times', 'B', 20);
					if( $img_width > 0 ) {
						$this->Cell($img_width, 10, '', 0);
					}
					$this->Cell(180-$img_width, 10, $this->header_name, 
						0, false, $align, 0, '', 0, false, 'M', 'M');
					$this->Ln(5);
				}
				$this->SetFont('times', '', 10);
				if( count($this->header_addr) > 0 ) {
					$address_lines = count($this->header_addr);
					if( $img_width > 0 ) {
						$this->Cell($img_width, ($address_lines*5), '', 0);
					}
					$this->MultiCell(180-$img_width, $address_lines, implode("\n", $this->header_addr), 
						0, $align, 0, 0, '', '', true, 0, false, true, 0, 'M', false);
					$this->Ln();
				}
			}

			//
			// Output the receipt details which should be at the top of each page.
			//
/*			$this->SetCellPadding(2);
			if( count($this->header_details) <= 6 ) {
				if( $this->header_name == '' && count($this->header_addr) == 0 ) {
					$this->Ln($this->header_height+6);
				} elseif( $this->header_name == '' && count($this->header_addr) > 0 ) {
					$used_space = 4 + count($this->header_addr)*5;
					if( $used_space < 30 ) {
						$this->Ln(30-$used_space+5);
					} else {
						$this->Ln(7);
					}
				} elseif( $this->header_name != '' && count($this->header_addr) > 0 ) {
					$used_space = 10 + count($this->header_addr)*5;
					if( $used_space < 30 ) {
						$this->Ln(30-$used_space+6);
					} else {
						$this->Ln(5);
					}
				} elseif( $this->header_name != '' && count($this->header_addr) == 0 ) {
					$this->Ln(25);
				}
				$this->SetFont('times', '', 10);
				$num_elements = count($this->header_details);
				if( $num_elements == 3 ) {
					$w = array(60,60,60);
				} elseif( $num_elements == 4 ) {
					$w = array(45,45,45,45);
				} elseif( $num_elements == 5 ) {
					$w = array(36,36,36,36,36);
				} else {
					$w = array(30,30,30,30,30,30);
				}
				$lh = 6;
				$this->SetFont('', 'B');
				for($i=0;$i<$num_elements;$i++) {
					if( $this->header_details[$i]['label'] != '' ) {
						$this->SetFillColor(224);
						$this->Cell($w[$i], $lh, $this->header_details[$i]['label'], 1, 0, 'C', 1);
					} else {
						$this->SetFillColor(255);
						$this->Cell($w[$i], $lh, '', 'T', 0, 'C', 1);
					}
				}
				$this->Ln();
				$this->SetFillColor(255);
				$this->SetFont('');
				for($i=0;$i<$num_elements;$i++) {
					if( $this->header_details[$i]['label'] != '' ) {
						$this->Cell($w[$i], $lh, $this->header_details[$i]['value'], 1, 0, 'C', 1);
					} else {
						$this->Cell($w[$i], $lh, '', 0, 0, 'C', 1);
					}
				}
				$this->Ln();
			} */
		}

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
//			$this->SetY(-15);
//			// Set font
//			$this->SetFont('helvetica', 'I', 8);
//			if( isset($this->settings['receipt-footer-message']) 
//				&& $this->settings['receipt-footer-message'] != '' ) {
//				$this->Cell(90, 10, $this->settings['receipt-footer-message'],
//					0, false, 'L', 0, '', 0, false, 'T', 'M');
//				$this->Cell(90, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
//					0, false, 'R', 0, '', 0, false, 'T', 'M');
//			} else {
//				// Center the page number if no footer message.
//				$this->Cell(0, 10, 'Page ' . $this->pageNo().'/'.$this->getAliasNbPages(), 
//					0, false, 'C', 0, '', 0, false, 'T', 'M');
//			}
		}
	}

	//
	// Start a new document
	//
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	//
	// Figure out the header business name and address information
	//
	$pdf->header_height = 0;
	$pdf->header_name = '';
	if( !isset($settings['receipt-header-contact-position'])
		|| $settings['receipt-header-contact-position'] != 'off' ) {
		if( !isset($settings['receipt-header-business-name'])
			|| $settings['receipt-header-business-name'] == 'yes' ) {
			$pdf->header_name = $business_details['name'];
			$pdf->header_height = 8;
		}
		if( !isset($settings['receipt-header-business-address'])
			|| $settings['receipt-header-business-address'] == 'yes' ) {
			if( isset($business_details['contact.address.street1']) 
				&& $business_details['contact.address.street1'] != '' ) {
				$pdf->header_addr[] = $business_details['contact.address.street1'];
			}
			if( isset($business_details['contact.address.street2']) 
				&& $business_details['contact.address.street2'] != '' ) {
				$pdf->header_addr[] = $business_details['contact.address.street2'];
			}
			$city = '';
			if( isset($business_details['contact.address.city']) 
				&& $business_details['contact.address.city'] != '' ) {
				$city .= $business_details['contact.address.city'];
			}
			if( isset($business_details['contact.address.province']) 
				&& $business_details['contact.address.province'] != '' ) {
				$city .= ($city!='')?', ':'';
				$city .= $business_details['contact.address.province'];
			}
			if( isset($business_details['contact.address.postal']) 
				&& $business_details['contact.address.postal'] != '' ) {
				$city .= ($city!='')?'  ':'';
				$city .= $business_details['contact.address.postal'];
			}
			if( $city != '' ) {
				$pdf->header_addr[] = $city;
			}
		}
/*		if( !isset($settings['receipt-header-business-phone'])
			|| $settings['receipt-header-business-phone'] == 'yes' ) {
			if( isset($business_details['contact.phone.number']) 
				&& $business_details['contact.phone.number'] != '' ) {
				$pdf->header_addr[] = 'phone: ' . $business_details['contact.phone.number'];
			}
			if( isset($business_details['contact.tollfree.number']) 
				&& $business_details['contact.tollfree.number'] != '' ) {
				$pdf->header_addr[] = 'phone: ' . $business_details['contact.tollfree.number'];
			}
		}
		if( !isset($settings['receipt-header-business-cell'])
			|| $settings['receipt-header-business-cell'] == 'yes' ) {
			if( isset($business_details['contact.cell.number']) 
				&& $business_details['contact.cell.number'] != '' ) {
				$pdf->header_addr[] = 'cell: ' . $business_details['contact.cell.number'];
			}
		}
		if( (!isset($settings['receipt-header-business-fax'])
			|| $settings['receipt-header-business-fax'] == 'yes')
			&& isset($business_details['contact.fax.number']) 
			&& $business_details['contact.fax.number'] != '' ) {
			$pdf->header_addr[] = 'fax: ' . $business_details['contact.fax.number'];
		}
		if( (!isset($settings['receipt-header-business-email'])
			|| $settings['receipt-header-business-email'] == 'yes')
			&& isset($business_details['contact.email.address']) 
			&& $business_details['contact.email.address'] != '' ) {
			$pdf->header_addr[] = $business_details['contact.email.address'];
		}
		if( (!isset($settings['receipt-header-business-website'])
			|| $settings['receipt-header-business-website'] == 'yes')
			&& isset($business_details['contact-website-url']) 
			&& $business_details['contact-website-url'] != '' ) {
			$pdf->header_addr[] = $business_details['contact-website-url'];
		}
*/
	}
	$pdf->header_height += (count($pdf->header_addr)*5);

	//
	// Set the minimum header height
	//
	if( $pdf->header_height < 30 ) {
		$pdf->header_height = 30;
	}

	//
	// Load the header image
	//
	if( isset($settings['receipt-header-image']) && $settings['receipt-header-image'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
		$rc = ciniki_images_loadImage($ciniki, $business_id, 
			$settings['receipt-header-image'], 'original');
		if( $rc['stat'] == 'ok' ) {
			$pdf->header_image = $rc['image'];
		}
	}

	$pdf->business_details = $business_details;
	$pdf->settings = $settings;

	//
	// Determine the header details
	//
//	$pdf->header_details = array(
//		array('label'=>'Receipt No.', 'value'=>$donation['receipt_number']),
//		array('label'=>'Date Received', 'value'=>$donation['date_received']),
//		array('label'=>'Issued On', 'value'=>$donation['date_issued']),
//		array('label'=>'Location', 'value'=>$donation['location_issued']),
//		array('label'=>'Eligible Amount', 'value'=>$donation['amount_display']),
//		);

	//
	// Setup the PDF basics
	//
	$pdf->SetCreator('Ciniki');
	$pdf->SetAuthor($business_details['name']);
	$pdf->SetTitle('Receipt #' . $donation['receipt_number']);
	$pdf->SetSubject('');
	$pdf->SetKeywords('');

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, $pdf->header_height+15, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


	// set font
	$pdf->SetFont('times', 'BI', 10);
	$pdf->SetCellPadding(1);

	// add a page
	$pdf->AddPage();
	$pdf->SetFillColor(255);
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(51);
	$pdf->SetLineWidth(0.15);

	//
	// Determine the billing address information
	//
	$addr = array();
	if( isset($donation['name']) && $donation['name'] != '' ) {
		$addr[] = $donation['name'];
	}
	if( isset($donation['address1']) && $donation['address1'] != '' ) {
		$addr[] = $donation['address1'];
	}
	if( isset($donation['address2']) && $donation['address2'] != '' ) {
		$addr[] = $donation['address2'];
	}
	$city = '';
	if( isset($donation['city']) && $donation['city'] != '' ) {
		$city = $donation['city'];
	}
	if( isset($donation['province']) && $donation['province'] != '' ) {
		$city .= (($city!='')?', ':'') . $donation['province'];
	}
	if( isset($donation['postal']) && $donation['postal'] != '' ) {
		$city .= (($city!='')?',  ':'') . $donation['postal'];
	}
	if( $city != '' ) {
		$addr[] = $city;
	}
	if( isset($donation['country']) && $donation['country'] != '' ) {
		$addr[] = $donation['country'];
	}

	//
	// Output the details
	//
	$w = array(45, 45, 90);
	$lh = 6;
	$pdf->SetFillColor(255);
	$pdf->setCellPadding(0.5);
	$pdf->SetFont('', 'B');
	$pdf->Cell($w[0], $lh, 'Receipt Number:', 0, 0, 'R', 1);
	$pdf->SetFont('', '');
	$pdf->Cell($w[1], $lh, $donation['receipt_number'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[0])?$addr[0]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->SetFont('', 'B');
	$pdf->Cell($w[0], $lh, 'Amount Received:', 0, 0, 'R', 1);
	$pdf->SetFont('', '');
	$pdf->Cell($w[1], $lh, $donation['amount_display'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[1])?$addr[1]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->SetFont('', 'B');
	$pdf->Cell($w[0], $lh, 'Date Received:', 0, 0, 'R', 1);
	$pdf->SetFont('', '');
	$pdf->Cell($w[1], $lh, $donation['date_received'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[2])?$addr[2]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->SetFont('', 'B');
	$pdf->Cell($w[0], $lh, 'Date Issued:', 0, 0, 'R', 1);
	$pdf->SetFont('', '');
	$pdf->Cell($w[1], $lh, $donation['date_issued'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[3])?$addr[3]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->SetFont('', 'B');
	$pdf->Cell($w[0], $lh, 'Location Issued:', 0, 0, 'R', 1);
	$pdf->SetFont('', '');
	$pdf->Cell($w[1], $lh, $donation['location_issued'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[4])?$addr[4]:''), 0, 0, 'L', 1);
	$pdf->Ln();
	
	$w = array(50, 50, 80);
	if( isset($settings['receipt-thankyou-message']) && $settings['receipt-thankyou-message'] != '' ) {
		$pdf->SetFont('', 'B');
		$pdf->Cell($w[0]+$w[1], $lh*2, $settings['receipt-thankyou-message'], 0, 0, 'L', 1);
		$pdf->Cell($w[2], $lh*2, '', 0, 0, 'L', 1);
	} else {
		$pdf->Cell($w[0]+$w[1], $lh*2, '', 0, 0, 'L', 1);
		$pdf->Cell($w[2], $lh*2, '', 0, 0, 'L', 1);
	}
	$pdf->Ln();
	$pdf->SetFont('', '');
	$pdf->setCellPadding(2);

	//
	// Output charity information and signature
	//
//	$pdf->Cell($w[0] + $w[1], $lh, 'Official ' . $donation['donation_year'] . ' Donation Receipt for Income Tax Purposes, Canada Revenue Agency: www.cra.gc.ca/charitiesandgiving', 0, 0, 'L', 1);
//	$pdf->Cell($w[0] + $w[1], $lh, 'Official ' . $donation['donation_year'] . ' Donation Receipt for Income Tax Purposes', 0, 0, 'L', 1);
//	$pdf->Cell($w[2], $lh, '', 0, 0, 'L', 1);
//	$pdf->Ln();
	$pdf->Cell($w[0]+$w[1], $lh, 'Charity BN/Registration #: ' . $settings['receipt-charity-number'], 0, 0, 'L', 1);
//	$pdf->Cell($w[2], $lh, '', 0, 0, 'L', 1);
//	$pdf->Ln();

//	$pdf->Cell($w[0] + $w[1], $lh, 'Canada Revenue Agency: www.cra.gc.ca/charitiesandgiving', 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, $settings['receipt-signing-officer'], 'T', 0, 'R', 1); 
	$pdf->Ln(10);
	$pdf->Cell(180, $lh, 'Official ' . $donation['donation_year'] . ' Donation Receipt for Income Tax Purposes, Canada Revenue Agency: www.cra.gc.ca/charitiesandgiving', 0, 0, 'C', 1);
	$pdf->Ln(10);

	//
	// Separator between official receipt and summary for customer to keep
	//
	$pdf->Cell(180, $lh, 'detach and retain for your records', array('T'=>array('dash'=>4, 'color'=>array(125,125,125))), 0, 'C', 1);

	$pdf->setCellPadding(1);
	$pdf->Ln(10);

	$pdf->Header();
	$pdf->Ln(15);

	$w = array(45, 45, 90);
	$pdf->Cell($w[0], $lh, 'Receipt Number:', 0, 0, 'R', 1);
	$pdf->Cell($w[1], $lh, $donation['receipt_number'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[0])?$addr[0]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->Cell($w[0], $lh, 'Eligible Amount:', 0, 0, 'R', 1);
	$pdf->Cell($w[1], $lh, $donation['amount_display'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[1])?$addr[1]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->Cell($w[0], $lh, 'Date Received:', 0, 0, 'R', 1);
	$pdf->Cell($w[1], $lh, $donation['date_received'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[2])?$addr[2]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->Cell($w[0], $lh, 'Date Issued:', 0, 0, 'R', 1);
	$pdf->Cell($w[1], $lh, $donation['date_issued'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[3])?$addr[3]:''), 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->Cell($w[0], $lh, 'Location Issued:', 0, 0, 'R', 1);
	$pdf->Cell($w[1], $lh, $donation['location_issued'], 0, 0, 'L', 1);
	$pdf->Cell($w[2], $lh, (isset($addr[4])?$addr[4]:''), 0, 0, 'L', 1);
	$pdf->Ln();
	
	if( isset($settings['receipt-thankyou-message']) && $settings['receipt-thankyou-message'] != '' ) {
		$pdf->SetFont('', 'B');
		$pdf->Cell(180, $lh*2, $settings['receipt-thankyou-message'], 0, 0, 'C', 1);
	} else {
		$pdf->Cell(180, $lh, '', 0, 0, 'C', 1);
	}
	$pdf->SetFont('', '');
	$pdf->Ln();

	//
	// Output charity information and signature
	//
	$pdf->Cell($w[0]+$w[1], $lh, 'Charity BN/Registration #: ' . $settings['receipt-charity-number'], 0, 0, 'L', 1);
	$pdf->Ln();

	$pdf->Cell($w[0] + $w[1], $lh, 'Canada Revenue Agency: www.cra.gc.ca/charitiesandgiving', 0, 0, 'L', 1);
	$pdf->Ln();

	// ---------------------------------------------------------

	//Close and output PDF document
	$pdf->Output('receipt_' . $donation['receipt_number'] . '.pdf', 'D');

	return array('stat'=>'exit');
}
?>
