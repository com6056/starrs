<div class="item_container">
	<form method="POST" class="input_form">
		<label for="type">Record Type: </label><input type="text" name="type" value="<?echo $type;?>" class="input_form_input" readonly />
		<label for="address">Address: </label><input type="text" name="address" value="<?echo ($addr->get_dynamic() == TRUE)?"Dynamic":$addr->get_address();?>" class="input_form_input" readonly />
		<label for="alias">Alias: </label><input type="text" name="alias" class="input_form_input" /><br>
		<label for="hostname">Hostname: </label><input type="text" name="hostname" class="input_form_input" /><br>
		<label for="zone">Domain: </label>
		<select name="zone" class="input_form_input">
			<? foreach ($zones as $zone) {
				echo '<option value="'.$zone->get_zone().'">'.$zone->get_zone().'</option>';
			} ?>
		</select>
		<label for="priority">Priority: </label><input type="text" name="priority" class="input_form_input" /><br>
		<label for="weight">Weight: </label><input type="text" name="weight" class="input_form_input" /><br>
		<label for="port">Port: </label><input type="text" name="port" class="input_form_input" /><br>
		<label for="ttl">TTL: </label><input type="text" name="ttl" class="input_form_input" />
		<?
		// Owner input
		if(isset($admin)) {?>
			<label for="owner">Owner: </label><input type="text" name="owner" value="<?echo $user;?>" class="input_form_input" />
		<?}
		else {?>
			<input type="hidden" name="owner" value="<?echo $user;?>" class="input_form_input" />
		<?}
		
		// Submit button
		?>
		<label for="submit">&nbsp;</label><input type="submit" name="recordSubmit" value="Create" class="input_form_submit"/>
	</form>
</div>