/* Privileges */

CREATE ROLE "impulse_client";
CREATE ROLE "impulse_admin";

/* Schemas
	Allow access to the schemas
*/
GRANT USAGE ON SCHEMA "api" TO "impulse_client";
GRANT USAGE ON SCHEMA "dhcp" TO "impulse_client";
GRANT USAGE ON SCHEMA "dns" TO "impulse_client";
GRANT USAGE ON SCHEMA "firewall" TO "impulse_client";
GRANT USAGE ON SCHEMA "ip" TO "impulse_client";
GRANT USAGE ON SCHEMA "management" TO "impulse_client";
GRANT USAGE ON SCHEMA "network" TO "impulse_client";
GRANT USAGE ON SCHEMA "systems" TO "impulse_client";

/* System Data
	Clients should never be able to modify these. They are for administrators only (superuser)
*/
GRANT SELECT ON "firewall"."transports" TO "impulse_client";
GRANT SELECT ON "ip"."range_uses" TO "impulse_client";
GRANT SELECT ON "systems"."device_types" TO "impulse_client";
GRANT SELECT ON "systems"."os_family" TO "impulse_client";
GRANT SELECT ON "dhcp"."config_types" TO "impulse_client";
GRANT SELECT ON "systems"."os" TO "impulse_client";
GRANT SELECT ON "network"."switchport_types" TO "impulse_client";
GRANT SELECT ON "firewall"."programs" TO "impulse_client";
GRANT SELECT ON "dns"."types" TO "impulse_client";

/* User Data
	This is all the stuff that clients can (depending on user permission level) modify
*/
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."metahosts" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dhcp"."class_options" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."defaults" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."rules" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "ip"."subnets" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "ip"."ranges" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."ns" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "network"."switchports" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "systems"."interface_addresses" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dhcp"."classes" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "systems"."systems" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dhcp"."subnet_options" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."metahost_members" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."pointers" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."mx" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."zones" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."keys" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "ip"."addresses" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."txt" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "dns"."a" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."systems" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "systems"."interfaces" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "management"."configuration" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."software" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "firewall"."metahost_rules" TO "impulse_client";
GRANT SELECT,INSERT,UPDATE,DELETE ON "management"."processes" TO "impulse_client";

/* Special Data
	Read or Write only, not both
*/
GRANT SELECT,INSERT ON "management"."log_master" TO "impulse_client";
GRANT SELECT ON "management"."output" TO "impulse_client";