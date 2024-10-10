<?php
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;

class Zugferd extends BriefpapierCustom {
  /** @var string $doctype */
  public $doctype;
  /** @var int $doctypeid */
  public $doctypeid;

  /**
   *
   * @param Application $app
   * @param string|int  $projekt
   * @param array       $styleData
   */
  public function __construct($app,$projekt="",$styleData=null)
  {
    $this->app=$app;
    //parent::Briefpapier();
    $this->doctype="rechnung";
    $this->doctypeOrig="Rechnung";
    parent::__construct($this->app,$projekt,$styleData);
  }

    public function CreateZugferdXML($id)
    {

        $data = $this->app->DB->SelectRow(
            "SELECT r.adresse, if(r.auftragid > 0,a.belegnr,r.auftrag) as auftrag, r.buchhaltung, r.bearbeiter, 
             r.vertrieb, r.lieferschein AS lieferscheinid, r.projekt, DATE_FORMAT(r.datum,'%Y%m%d') AS datum, 
             DATE_FORMAT(r.mahnwesen_datum,'%d.%m.%Y') AS mahnwesen_datum, 
             DATE_FORMAT(r.lieferdatum,'%d.%m.%Y') AS lieferdatum, r.belegnr, r.bodyzusatz, r.doppel, 
             r.freitext, r.systemfreitext, r.ustid, r.typ, r.keinsteuersatz, r.soll, r.ist, r.land, 
             r.zahlungsweise, r.zahlungsstatus, r.zahlungszieltage, r.zahlungszieltageskonto, 
             r.zahlungszielskonto, r.ohne_briefpapier, r.ihrebestellnummer, r.ust_befreit, r.waehrung, 
             r.versandart, 
             DATE_FORMAT(DATE_ADD(r.datum, INTERVAL r.zahlungszieltage DAY),'%d.%m.%Y') AS zahlungdatum, 
             DATE_FORMAT(DATE_ADD(r.datum, INTERVAL r.zahlungszieltageskonto DAY),'%d.%m.%Y') AS zahlungszielskontodatum, 
             r.abweichendebezeichnung AS rechnungersatz, 
             r.kundennummer, r.sprache, r.schreibschutz, r.soll AS gesamtsumme,
             DATE_FORMAT(r.datum,'%Y%m%d') as datum2, r.telefon, r.email,
             a.name, a.strasse, a.plz, a.ort
             FROM rechnung r LEFT JOIN auftrag a ON a.id=r.auftragid LEFT JOIN adresse a2 ON a2.id = r.adresse WHERE r.id='$id' LIMIT 1"
          );

          extract($data,EXTR_OVERWRITE);
          $adresse = $data['adresse'];
          $auftrag = $data['auftrag'];
          $buchhaltung = $data['buchhaltung'];
          $bearbeiter = $data['bearbeiter'];
          $vertrieb = $data['vertrieb'];
          $lieferscheinid = $data['lieferscheinid'];
          $projekt = $data['projekt'];
          $datum = $data['datum'];
          $mahnwesen_datum = $data['mahnwesen_datum'];
          $lieferdatum = $data['lieferdatum'];
          $belegnr = $data['belegnr'];
          $bodyzusatz = $data['bodyzusatz'];
          $doppel = $data['doppel'];
          $freitext = $data['freitext'];
          $systemfreitext = $data['systemfreitext'];
          $ustid = $data['ustid'];
          $typ = $data['typ'];
          $keinsteuersatz = $data['keinsteuersatz'];
          $soll = $data['soll'];
          $ist = $data['ist'];
          $soll = $data['soll'];
          $land = $data['land'];
          $zahlungsweise = $data['zahlungsweise'];
          $zahlungsstatus = $data['zahlungsstatus'];
          $zahlungszieltage = $data['zahlungszieltage'];
          $zahlungszieltageskonto = $data['zahlungszieltageskonto'];
          $zahlungszielskonto = $data['zahlungszielskonto'];
          $versandart = $data['versandart'];
          $zahlungdatum = $data['zahlungdatum'];
          $zahlungszielskontodatum = $data['zahlungszielskontodatum'];
      
          $ihrebestellnummer = $data['ihrebestellnummer'];
          $ust_befreit = $data['ust_befreit'];
          $waehrung = $data['waehrung'];
          $ohne_briefpapier = $data['ohne_briefpapier'];
      
          $rechnungersatz = $data['rechnungersatz'];
          $kundennummer = $data['kundennummer'];
          $sprache = $data['sprache'];
          $schreibschutz = $data['schreibschutz'];
          $gesamtsumme = $data['gesamtsumme'];
          $datum2 = $data['datum2'];
          $email = $data['email'];
          $telefon = $data['telefon'];
          $kundenname = $data['name'];
          $kundenstr = $data['strasse'];
          $kundenplz = $data['plz'];
          $kundenort = $data['ort'];
    
       // echo "PLATZHALTER XXXXXXXXXX XXXXXXXXX XXXXXXXXX Ab hier wieder Daten: ";
        var_dump($data);

    $firmendaten = $this->app->DB->SelectRow("SELECT name FROM `firma` WHERE 1;");
        extract($firmendaten,EXTR_OVERWRITE);
        $firmenname = $firmendaten['name'];

        // SQL Rechnungspositionen

        $rechnungspositionendata = $this->app->DB->SelectArr("SELECT id, bezeichnung, preis, menge, nummer FROM `rechnung_position` WHERE rechnung = $id;");
        var_dump($rechnungspositionendata);
       extract($rechnungspositionendata,EXTR_OVERWRITE);
        $posid = $rechnungspositionendata['id'];
        $posbezeichnung = $rechnungspositionendata['bezeichnung'];
        
    //echo $posbezeichnung;

    $file = (dirname(__FILE__) . "/test1.xml");
    // Create an empty invoice document in the EN16931 profile
    $document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_EN16931);

    // Add invoice and position information
    $document
    ->setDocumentInformation("471102", "380", \DateTime::createFromFormat("Ymd", $datum), "EUR")
    ->setDocumentBusinessProcess('urn:fdc:peppol.eu:2017:poacc:billing:01:1.0')
    ->addDocumentNote($freitext)
    ->setDocumentSupplyChainEvent(\DateTime::createFromFormat('Ymd', $datum))
    ->setDocumentSeller($firmenname, "549910")
    ->addDocumentSellerGlobalId("4000001123452", "0088")
    ->addDocumentSellerTaxRegistration("FC", "201/113/40209")
    ->addDocumentSellerTaxRegistration("VA", $this->app->erp->Firmendaten('steuernummer'))
    ->setDocumentSellerAddress($this->app->erp->Firmendaten('strasse'), "", "", $this->app->erp->Firmendaten('plz'), $this->app->erp->Firmendaten('ort'), "DE")
    ->setDocumentSellerCommunication('EM', $this->app->erp->Firmendaten('email'))
    ->setDocumentSellerContact($vertrieb, '', ' ', ' ', $this->app->erp->Firmendaten('email'))
    ->setDocumentBuyer($kundenname, "GE2020211")
    ->setDocumentBuyerAddress($kundenstr, "", "", $kundenplz, $kundenort, "DE")
    ->setDocumentBuyerCommunication('EM', $email);
    if ($zahlungsweise === 'lastschrift') {
      $document
          ->addDocumentPaymentMeanToDirectDebit('DE02120300000000202051', '471102')
          ->setDocumentBuyerReference('04011000-12345ABCXYZ-86');
  }  
  $document
    ->addDocumentTax("S", "VAT", 275.0, 19.25, 7.0)
    ->addDocumentTax("S", "VAT", 198.0, 37.62, 19.0)
    ->setDocumentSummation($gesamtsumme, $gesamtsumme, 473.00, 0.0, 0.0, 473.00, 56.87, null, 0.0)
    ->addDocumentPaymentTerm("Zahlungszieldatum " . $zahlungszielskontodatum, null, '549910');

  $positionNumber = 1;
  //echo $posbezeichnung;
  //die(1);
foreach ($rechnungspositionendata as $position) {
    $posid = $position['id'];
    $posbezeichnung = $position['bezeichnung'];
    $pospreis = $position['preis'];
    $posmenge = $position['menge'];
    $posartnr = $position['nummer'];

    $document
        ->addNewPosition((string)$positionNumber)
        ->setDocumentPositionProductDetails($posbezeichnung, "", $posartnr, null, "0160", "400000000000" . $posid) 
        ->setDocumentPositionNetPrice($pospreis)
        ->setDocumentPositionQuantity($posmenge, "H87") 
        ->addDocumentPositionTax('S', 'VAT', 19)
        ->setDocumentPositionLineSummation($pospreis * $posmenge);
        
    $positionNumber++;
}
// Datei speichern
    $document->writeFile($file);}
  }