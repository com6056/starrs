<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . "controllers/interfaces.php");
require_once(APPPATH . "controllers/dns.php");

/**
 * This class contains the definition of an InterfaceAddress object. These
 * objects represent an address tied to the
 * specified address.
 */
class InterfaceAddress extends ImpulseObject {
	

	////////////////////////////////////////////////////////////////////////
	// ENUM-LIKE SILLYNESS
	
	// Enumeration values for address family
	public static $IPv4 = 4;
	public static $IPv6 = 6;
	
	////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	
	// string		The address bound to the interface 
	private $address;
	
	// string		The class of the address (all values are default so far)
	private $class;
	
	// string		A comment about the address
	private $comment;
	
	// string		The config type for the address
	// @todo: make a getValidConfigTypes method
	private $config;
	
	// enum(int)	The family the address belongs to (either IPv4 or v6). Can
	// be compared against using ::$IPv4 and ::$IPv6
	private $family;
	
	// string		The mac address that this address is bound to. Can be used
	// to lookup the matching InterfaceObject. 
	private $mac;
	
	// long			The unix timestamp that the interface will be renewed.
	private $renewDate;
	
	// bool			Is this address the primary for the interface
	private $isPrimary;
	
	// string		The name of the containing system
	private $systemName;
	
	// string		The name of the containing range
	private $range;

	
	////////////////////////////////////////////////////////////////////////
	// DNS RELATED VARIABLES
	
	// string				The FQDN that resolves to this address
	private $dnsFqdn;
	
	// AddressRecord		AddressRecord object of this address
	private $dnsAddressRecord;
	
	// array<PointerRecord>	All pointer (CNAME,SRV) records that resolve to this address
	private $dnsPointerRecords;
	
	// array<NsRecord>		All nameserver (NS) records that resolve to this address
	private $dnsNsRecords;
	
	// array<MxRecords>		All mail server (MX) records that resolve to this address
	private $dnsMxRecords;
	
	// array<TextRecord>		All TXT or SPF records that describe this address
	private $dnsTextRecords;
	
	////////////////////////////////////////////////////////////////////////
	// FIREWALL RELATED VARIABLES
	
	// bool					Firewall default action (t for deny, f for all)
	private $fwDefault;
	
	// array<FirewallRule>	All firewall rules that relate to this address
	private $fwRules;
	
	////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	
	/**
	 * Construct a new InterfaceAddress object using the given information
	 * @param 	string 	$address		The address bound to the address 
	 * @param 	string 	$class			The class of the address
	 * @param 	string	$config			How the address is configured
	 * @param 	string 	$mac			The mac address the interface address 
	 * 									is bound to
	 * @param 	long	$renewDate		Unix timestamp when the address renews
	 * @param	bool	$isPrimary		Is this address the primary for the interface?
	 * @param 	string	$comment		A comment about the address
	 * @param	long	$dateCreated	Unix timestamp when the address was created
	 * @param	long	$dateModified	Unix timestamp when the address was modifed
	 * @param	string	$lastModifier	The last user to modify the address
	 */
	public function __construct($address, $class, $config, $mac, $renewDate, $isPrimary, $comment, $dateCreated, $dateModified, $lastModifier) {
		// Chain into the parent
		parent::__construct($dateCreated, $dateModified, $lastModifier);
		
		// InterfaceAddress-specific stuff
		$this->address   = $address;
		$this->class     = $class;
		$this->config    = $config;
		$this->mac       = $mac;
		$this->renewDate = $renewDate;
		$this->comment   = $comment; 
		$this->isPrimary = $isPrimary;
		
		// Determine the family of the address based on whether there's a : or not
		$this->family  = (strpos($address, ':') === false) ? self::$IPv4 : self::$IPv6;
		
		// Initialize variables
		$this->dnsPointerRecords = array();
		$this->dnsNsRecords = array();
		$this->dnsMxRecords = array();
		$this->dnsTextRecords = array();
		$this->fwRules = array();
		try {
			$this->dnsAddressRecord = $this->CI->api->dns->get_address_record($this->address);
			$this->dnsFqdn = $this->dnsAddressRecord->get_hostname().".".$this->dnsAddressRecord->get_zone();
		}
		catch (ObjectNotFoundException $onfE) {
			$this->dnsAddressRecord = null;
			$this->dnsFqdn = null;
		}
		$this->fwDefault = $this->CI->api->firewall->get_firewall_default($this->address);
		$this->systemName = $this->CI->api->systems->get_interface_address_system($this->address);
		$this->range = $this->CI->api->ip->get_address_range($this->address);
	}
	
	////////////////////////////////////////////////////////////////////////
	// GETTERS
	
	public function get_address()         { return $this->address; }
	public function get_class()           { return $this->class; }
	public function get_config()          { return $this->config; }
	public function get_mac()             { return $this->mac; }
	public function get_renew_date()      { return $this->renewDate; }
	public function get_comment()         { return $this->comment; }
	public function get_family()          { return $this->family; }
	public function get_isprimary()       { return $this->isPrimary; }
	public function get_fqdn()            { return $this->dnsFqdn; }
	public function get_rules()           { return $this->fwRules; }
	public function get_fw_default()      { return $this->fwDefault; }
	public function get_address_record()  { return $this->dnsAddressRecord; }
	public function get_ns_records()      { return $this->dnsNsRecords; }
	public function get_mx_records()      { return $this->dnsMxRecords; }
	public function get_pointer_records() { return $this->dnsPointerRecords; }
	public function get_text_records()    { return $this->dnsTextRecords; }
	public function get_system_name()     { return $this->systemName; }
	public function get_range()           { return $this->range; }
	
	public function get_help() {
		return "Interfaces are assigned IP addresses in multiple ways from configured resource pools. 
		Each address is then configured with DNS records and Firewall Rules. Depending on your IP address
		and privilege level, certain records may or may not be available to you. Firewall rules can be 
		applied in \"standalone\" or \"metahost\" contexts. You will be warned if there are conflicts in 
		your firewall rules.";
	}
	
	public function get_start() {
		return "To get started, click a view above, then click Create.";
	}
	
	////////////////////////////////////////////////////////////////////////
	// SETTERS
	
	public function set_address($new) {
		$this->CI->api->systems->modify_interface_address($this->address, 'address', $new);	
		$this->address = $new; 
	}
	
	public function set_config($new) {
		$this->CI->api->systems->modify_interface_address($this->address, 'config', $new);	
		$this->config = $new; 
	}
	
	public function set_class($new) {
		$this->CI->api->systems->modify_interface_address($this->address, 'class', $new);	
		$this->class = $new; 
	}
	
	public function set_isprimary($new) {
		$this->CI->api->systems->modify_interface_address($this->address, 'isprimary', $new);	
		$this->isPrimary = $new; 
	}
	
	public function set_comment($new) {
		$this->CI->api->systems->modify_interface_address($this->address, 'comment', $new);	
		$this->comment = $new; 
	}
	
	////////////////////////////////////////////////////////////////////////
	// PRIVATE METHODS
	
	////////////////////////////////////////////////////////////////////////
	// PUBLIC METHODS
	
	
	
	/**
	 * Adds a firewall rule to the address
	 * @param FirewallRule	$rule	The rule object to add
	 */
	public function add_firewall_rule($rule) {
		// If it's not a rule, blow up
		if(!$rule instanceof FirewallRule) {
			throw new ObjectException("Cannot add a non-rule as a rule");
		}
		
		// Add the rule to the local array
		$this->fwRules[] = $rule;
	}
	
	public function set_address_record($addressRecord) {
		if(!$addressRecord instanceof AddressRecord) {
			throw new ObjectException("Cannot add a non-address-record as a address-record");
		}
		
		$this->dnsAddressRecord = $addressRecord;
	}

    /**
     * Add a pointer record for this address
     * @param $pointerRecord    The record object to add
     */
    public function add_pointer_record($pointerRecord) {
		// If it's not a proper record, blow up
		if(!$pointerRecord instanceof PointerRecord) {
			throw new ObjectException("Cannot add a non-pointer-record as a pointer-record");
		}

		// Add the record to the local array
		$this->dnsPointerRecords[] = $pointerRecord;
	}

    /**
     * Add a TXT or SPF record for this address
     * @param $txtRecord    The record object to add
     */
    public function add_text_record($textRecord) {
		// If it's not a proper record, blow up
		if(!$textRecord instanceof TextRecord) {
			throw new ObjectException("Cannot add a non-txt-record as a text-record");
		}

		// Add the record to the local array
		$this->dnsTextRecords[] = $textRecord;
	}

    /**
     * Add a NS record for this address
     * @param $nsRecord     The record object to add
     */
    public function add_ns_record($nsRecord) {
		// If it's not a proper record, blow up
		if(!$nsRecord instanceof NsRecord) {
			throw new ObjectException("Cannot add a non-ns-record as a ns-record");
		}

		// Add the record to the local array
		$this->dnsNsRecords[] = $nsRecord;
	}

    /**
     * Add a MX record for this address
     * @param $mxRecord     The record object to add
     */
    public function add_mx_record($mxRecord) {
		// If it's not a proper record, blow up
		if(!($mxRecord instanceof MxRecord)) {
			throw new ObjectException("Cannot add a non-mx-record as a mx-record");
		}

		// Add the record to the local array
		$this->dnsMxRecords[] = $mxRecord;
	}
	
	//@todo: finish this
	public function add_record($record) {
		echo get_class($record);
		switch (get_class($record)) {
			case "AddressRecord":
				$this->set_address_record($record);
				break;
			case "NsRecord":
				$this->add_ns_record($record);
				break;
			default:
				throw new ObjectException("Unsupported DNS record given");
		}
	}
}

/* End of file InterfaceAddress.php */
/* Location: ./application/libraries/objects/InterfaceAddress.php */