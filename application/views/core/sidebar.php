<div class="sidebar">
	<div id="sidetree">
		<ul class="treeview" id="tree">
			<li><a href="/"><span><strong>Home</strong></span></a></li>
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/systems"><span><strong>Systems</strong></span></a>
				<ul style="display: none;">
					<?echo $sidebar->load_owned_system_view_data();?>
					<li class="expandable last"><div class="hitarea expandable-hitarea"></div><span><strong>Other</strong></span>
						<ul style="display: none;">
							<?echo $sidebar->load_other_system_view_data();?>
						</ul>
					</li>
				</ul>
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>Metahosts</strong></span>
				<ul style="display: none;">
					<?echo $sidebar->load_owned_metahost_view_data();?>
					<li class="expandable last"><div class="hitarea expandable-hitarea"></div><a href="/metahosts/all">Other</a>
						<ul style="display: none;">
							<?echo $sidebar->load_other_metahost_view_data();?>
						</ul>
					</li>
				</ul>
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>Statistics</strong></span>
				
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>Resources</strong></span>
				<ul style="display: none;">
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/resources/keys">Keys</a>
						<ul style="display: none;">
							<?
							echo $sidebar->load_owned_key_view_data();
							echo $sidebar->load_other_key_view_data();
							?>
						</ul>
					</li>
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/resources/zones">Zones</a>
						<ul style="display: none;">
							<?
							echo $sidebar->load_owned_zone_view_data();
							echo $sidebar->load_other_zone_view_data();
							?>
						</ul>
					</li>
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/resources/subnets">Subnets</a>
						<ul style="display: none;">
							<?
							echo $sidebar->load_owned_subnet_view_data();
							echo $sidebar->load_other_subnet_view_data();
							?>
						</ul>
					</li>
					<li class="expandable last"><div class="hitarea expandable-hitarea"></div><a href="/resources/ranges">Ranges</a>
						<ul style="display: none;">
							<?
							echo $sidebar->load_range_view_data();
							?>
						</ul>
					</li>
				</ul>
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>Administration</strong></span>
				<ul style="display: none;">
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/admin/configuration/view/site">Site Configuration</a></li>
				</ul>
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>DHCP</strong></span>
				<ul style="display: none;">
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/dhcp/classes">Classes</a>
						<ul style="display: none;">
							<?
							echo $sidebar->load_class_view_data();
							?>
						</ul>
					</li>
					<li class="last"><a href="/dhcp/options/view/global">Global Options</a></li>
				</ul>
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>Reference</strong></span>
				<ul style="display: none;">
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/reference/api">API</a></li>
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/reference/reference/help">Help</a></li>
				</ul>
			</li>
			
			<li class="expandable"><div class="hitarea expandable-hitarea"></div><span><strong>Output</strong></span>
				<ul style="display: none;">
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/output/view/dhcpd.conf">DHCPD Config</a></li>
					<li class="expandable"><div class="hitarea expandable-hitarea"></div><a href="/output/view/fw_default_queue">Firewall Default Queue</a></li>
				</ul>
			</li>
		</ul>
	</div>
	<div id="sidetreecontrol"> <a href="?#">Collapse All</a> | <a href="?#">Expand All</a> </div>
</div>