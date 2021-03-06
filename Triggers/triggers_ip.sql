/* Trigger subnets_insert 
	1) Check for larger subnets
	2) Check for smaller subnets
	3) Check for existing addresses
	4) Autogenerate addresses & firewall default
*/
CREATE OR REPLACE FUNCTION "ip"."subnets_insert"() RETURNS TRIGGER AS $$
	DECLARE
		RowCount INTEGER;
		SubnetAddresses RECORD;
	BEGIN
		-- Check for larger subnets
		SELECT COUNT(*) INTO RowCount
		FROM "ip"."subnets"
		WHERE NEW."subnet" << "ip"."subnets"."subnet";
		IF (RowCount > 1) THEN
			RAISE EXCEPTION 'A larger existing subnet was detected. Nested subnets are not supported.';
		END IF;

		-- Check for smaller subnets
		SELECT COUNT(*) INTO RowCount
		FROM "ip"."subnets"
		WHERE NEW."subnet" >> "ip"."subnets"."subnet";
		IF (RowCount > 0) THEN
			RAISE EXCEPTION 'A smaller existing subnet was detected. Nested subnets are not supported.';
		END IF;
		
		-- Check for existing addresses
		SELECT COUNT(*) INTO RowCount
		FROM "ip"."addresses"
		WHERE "ip"."addresses"."address" << NEW."subnet";
		IF RowCount >= 1 THEN
			RAISE EXCEPTION 'Existing addresses detected for your subnet. Modify the existing subnet.';
		END IF;

		-- Autogenerate addresses & firewall default
		IF NEW."autogen" IS TRUE THEN
			FOR SubnetAddresses IN SELECT api.get_subnet_addresses(NEW."subnet") LOOP
				INSERT INTO "ip"."addresses" ("address") VALUES (SubnetAddresses.get_subnet_addresses);
			END LOOP;
		END IF;
		
		-- Done
		RETURN NEW;
	END;
$$ LANGUAGE 'plpgsql';
COMMENT ON FUNCTION "ip"."subnets_insert"() IS 'Create a subnet';

/* Trigger - subnets_update 
	1) Check for larger subnets
	2) Check for smaller subnets
	3) Check for existing addresses
	4) Autogenerate addresses
*/
CREATE OR REPLACE FUNCTION "ip"."subnets_update"() RETURNS TRIGGER AS $$
	DECLARE
		RowCount INTEGER;
		SubnetAddresses RECORD;
	BEGIN
		IF NEW."subnet" != OLD."subnet" THEN
			-- Check for larger subnets
			SELECT COUNT(*) INTO RowCount
			FROM "ip"."subnets"
			WHERE NEW."subnet" << "ip"."subnets"."subnet";
			IF (RowCount > 0) THEN
				RAISE EXCEPTION 'A larger existing subnet was detected. Nested subnets are not supported.';
			END IF;

			-- Check for smaller subnets
			SELECT COUNT(*) INTO RowCount
			FROM "ip"."subnets"
			WHERE NEW."subnet" >> "ip"."subnets"."subnet"
			AND OLD."subnet" <> "ip"."subnets"."subnet";
			IF (RowCount > 0) THEN
				RAISE EXCEPTION 'A smaller existing subnet was detected. Nested subnets are not supported.';
			END IF;
			
			-- Check for existing addresses
			SELECT COUNT(*) INTO RowCount
			FROM "ip"."addresses"
			WHERE "ip"."addresses"."address" << NEW."subnet";
			IF RowCount >= 1 THEN
				RAISE EXCEPTION 'Existing addresses detected for your subnet. Modify the existing subnet.';
			END IF;
		END IF;

		-- Autogenerate addresses
		IF NEW."autogen" != OLD."autogen" THEN
			IF NEW."autogen" IS TRUE THEN
				DELETE FROM "ip"."addresses" WHERE "ip"."addresses"."address" << OLD."subnet";
				FOR SubnetAddresses IN SELECT api.get_subnet_addresses(NEW."subnet") LOOP
					INSERT INTO "ip"."addresses" ("address") VALUES (SubnetAddresses.get_subnet_addresses);
				END LOOP;
			END IF;
		END IF;
		
		-- Done
		RETURN NEW;
	END;
$$ LANGUAGE 'plpgsql';
COMMENT ON FUNCTION "ip"."subnets_update"() IS 'Modify an existing new subnet';

/* Trigger - subnets_delete 
	1) Check for inuse addresses
	2) Delete autogenerated addresses
*/
CREATE OR REPLACE FUNCTION "ip"."subnets_delete"() RETURNS TRIGGER AS $$
	DECLARE
		RowCount INTEGER;
	BEGIN
		-- Check for inuse addresses
		SELECT COUNT(*) INTO RowCount
		FROM "ip"."addresses"
		WHERE EXISTS (
			SELECT "address" 
			FROM "systems"."interface_addresses" 
			WHERE "systems"."interface_addresses"."address" = "ip"."addresses"."address" )
		AND "ip"."addresses"."address" << OLD."subnet";
		IF (RowCount >= 1) THEN
			RAISE EXCEPTION 'Inuse addresses found. Aborting delete.';
		END IF;

		-- Delete autogenerated addresses
		IF OLD."autogen" = TRUE THEN
			DELETE FROM "ip"."addresses" WHERE "address" << OLD."subnet";
		END IF;

		-- Done
		RETURN OLD;
	END;
$$ LANGUAGE 'plpgsql';
COMMENT ON FUNCTION "ip"."subnets_delete"() IS 'You can only delete a subnet if no addresses from it are inuse.';

/* Trigger - addresses_insert 
	1) Check for existing default action (should never happen)
*/
CREATE OR REPLACE FUNCTION "ip"."addresses_insert"() RETURNS TRIGGER AS $$
	DECLARE
		RowCount INTEGER;
	BEGIN
		-- Done
		RETURN NEW;
	END;
$$ LANGUAGE 'plpgsql';
COMMENT ON FUNCTION "ip"."addresses_insert"() IS 'Activate a new IP address in the application';

/* Trigger - ranges_insert 
	1) Check for illegal addresses
	2) Check address vs subnet
	3) Check valid range
	4) Check address existance
	5) Define lower boundary for range
	6) Define upper boundry for range
	7) Check for range spanning
*/
CREATE OR REPLACE FUNCTION "ip"."ranges_insert"() RETURNS TRIGGER AS $$
	DECLARE
		LowerBound INET;
		UpperBound INET;
		query_result RECORD;
		RowCount INTEGER;
	BEGIN
		-- Check for illegal addresses
		IF host(NEW."subnet") = host(NEW."first_ip") THEN
			RAISE EXCEPTION 'You cannot have a boundry that is the network identifier';
		END IF;
		
		-- Check address vs subnet
		IF NOT NEW."first_ip" << NEW."subnet" OR NOT NEW."last_ip" << NEW."subnet" THEN
			RAISE EXCEPTION 'Range addresses must be inside the specified subnet';
		END IF;

		-- Check valid range
		IF NEW."first_ip" >= NEW."last_ip" THEN
			RAISE EXCEPTION 'First address is larger or equal to last address.';
		END IF;

		-- IPv6
		IF family(NEW."subnet") = 6 THEN
			INSERT INTO "ip"."addresses" ("address") (SELECT * FROM "api"."get_range_addresses"(NEW."first_ip", NEW."last_ip") AS "potential" WHERE "potential" NOT IN (SELECT "address" FROM "ip"."addresses" WHERE "ip"."addresses"."address" << NEW."subnet"));
		END IF;
		
		-- Check address existance
		SELECT COUNT(*) INTO RowCount
		FROM "ip"."addresses"
		WHERE "ip"."addresses"."address" = NEW."first_ip";
		IF (RowCount != 1) THEN
			RAISE EXCEPTION 'First address (%) not found in address pool.',NEW."first_ip";
		END IF;
		
		SELECT COUNT(*) INTO RowCount
		FROM "ip"."addresses"
		WHERE "ip"."addresses"."address" = NEW."last_ip";
		IF (RowCount != 1) THEN
			RAISE EXCEPTION 'Last address (%) not found in address pool.',NEW."last_ip";
		END IF;

		-- Define lower boundary for range
		-- Loop through all ranges and find what is near the new range
		FOR query_result IN SELECT "first_ip","last_ip" FROM "ip"."ranges" WHERE "subnet" = NEW."subnet" ORDER BY "last_ip" LOOP
			IF NEW."first_ip" >= query_result.first_ip AND NEW."first_ip" <= query_result.last_ip THEN
				RAISE EXCEPTION 'First address out of bounds.';
			ELSIF NEW."first_ip" > query_result.last_ip THEN
				LowerBound := query_result.last_ip;
			END IF;
			IF NEW."last_ip" >= query_result.first_ip AND NEW."last_ip" <= query_result.last_ip THEN
				RAISE EXCEPTION 'Last address is out of bounds';
			END IF;
		END LOOP;

		-- Define upper boundry for range
		SELECT "first_ip" INTO UpperBound
		FROM "ip"."ranges"
		WHERE "first_ip" >= LowerBound
		ORDER BY "first_ip" LIMIT 1;

		-- Check for range spanning
		IF NEW."last_ip" >= UpperBound THEN
			RAISE EXCEPTION 'Last address is out of bounds';
		END IF;

		-- Done
		RETURN NEW;
	END;
$$ LANGUAGE 'plpgsql';
COMMENT ON FUNCTION "ip"."ranges_insert"() IS 'Insert a new range of addresses for use';

/* Trigger - ranges_update
	1) Check for illegal addresses
	2) Check address vs subnet
	3) Check valid range
	4) Check address existance
	5) Define lower boundary for range
	6) Define upper boundry for range
	7) Check for range spanning
*/
CREATE OR REPLACE FUNCTION "ip"."ranges_update"() RETURNS TRIGGER AS $$
	DECLARE
		LowerBound INET;
		UpperBound INET;
		query_result RECORD;
		RowCount INTEGER;
	BEGIN
		IF NEW."first_ip" != OLD."first_ip" OR NEW."last_ip" != OLD."last_ip" THEN
			-- Check for illegal addresses
			IF host(NEW."subnet") = host(NEW."first_ip") THEN
				RAISE EXCEPTION 'You cannot have a boundry that is the network identifier';
			END IF;
			
			-- Check address vs subnet
			IF NOT NEW."first_ip" << NEW."subnet" OR NOT NEW."last_ip" << NEW."subnet" THEN
				RAISE EXCEPTION 'Range addresses must be inside the specified subnet';
			END IF;

			-- Check valid range
			IF NEW."first_ip" >= NEW."last_ip" THEN
				RAISE EXCEPTION 'First address is larger or equal to last address.';
			END IF;
			
			-- Check address existance
			SELECT COUNT(*) INTO RowCount
			FROM "ip"."addresses"
			WHERE "ip"."addresses"."address" = NEW."first_ip";
			IF (RowCount != 1) THEN
				RAISE EXCEPTION 'First address (%) not found in address pool.',NEW."first_ip";
			END IF;
			
			SELECT COUNT(*) INTO RowCount
			FROM "ip"."addresses"
			WHERE "ip"."addresses"."address" = NEW."last_ip";
			IF (RowCount != 1) THEN
				RAISE EXCEPTION 'Last address (%) not found in address pool.',NEW."last_ip";
			END IF;

			-- Define lower boundary for range
			-- Loop through all ranges and find what is near the new range
			FOR query_result IN SELECT "first_ip","last_ip" FROM "ip"."ranges" WHERE "subnet" = NEW."subnet" AND "first_ip" != OLD."first_ip" ORDER BY "last_ip" LOOP
				-- Check if the new first_ip is contained within the next lower range
				IF NEW."first_ip" >= query_result.first_ip AND NEW."first_ip" <= query_result.last_ip THEN
					RAISE EXCEPTION 'First address out of bounds.';

				--Check if the new last_ip is contained with the next lower range
				ELSIF NEW."last_ip" >= query_result.first_ip AND NEW."last_ip" <= query_result.last_ip THEN
					RAISE EXCEPTION 'Last address LOLOLOL is out of bounds';
				ELSIF NEW."first_ip" > query_result.last_ip THEN
					LowerBound := query_result.last_ip;
				END IF;
				
			END LOOP;

			-- Define upper boundry for range
			SELECT "first_ip" INTO UpperBound
			FROM "ip"."ranges"
			WHERE "first_ip" > LowerBound
			AND "name" != NEW."name"
			ORDER BY "first_ip" DESC LIMIT 1;

			-- Check for range spanning
			IF NEW."last_ip" >= UpperBound THEN
				RAISE EXCEPTION 'Last address HAHAH is out of bounds';
			END IF;
		END IF;
		-- Done
		RETURN NEW;
	END;
$$ LANGUAGE 'plpgsql';
COMMENT ON FUNCTION "ip"."ranges_update"() IS 'Alter a range of addresses for use';
