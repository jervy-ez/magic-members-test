<form action="#" method="post">
	<table>
		<thead>
			<tr>
				<th colspan="2"><h1>API TEST CASE</h1></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Resource Base URL</td>
				<td>
					<input type="text" size="100" name="resource_baseurl" value="<?php echo isset($_POST['resource_baseurl']) ? $_POST['resource_baseurl'] : 'http://localhost/magicmediagroup/dev'?>">
				</td>
			</tr>
			<tr>
				<td>API Key</td>
				<td>
					<input type="text" size="100" name="api_key" value="<?php echo isset($_POST['api_key']) ? $_POST['api_key'] : 'gvkWfz8B'?>">
				</td>
			</tr>
			<tr>
				<td>Test Case</td>
				<td>
					<?php 
					// path
					$path = 'test_cases';//str_replace('html','test_cases',dirname(__FILE__)) ;
					// files
					$case_files = glob($path . DIRECTORY_SEPARATOR . 'test_case_*');
					// init
					$cases= array();
					// loop
					foreach($case_files as $file):
						// base name
						$case = basename($file, '.php');
						// name
						$case_name = ucwords(str_replace('_', ' ', $case));
						// id
						$case_id = str_replace('test_case_', '', $case); 
						// set
						$cases[$case_id] = $case_name;
					endforeach;?>
					<select name="test_case">
						<?php foreach($cases as $case_id=>$case_name):?>
						<option value="<?php echo $case_id?>" <?php echo (isset($_POST['test_case']) && $_POST['test_case'] == $case_id) ? 'selected' : ''?>><?php echo $case_name?></option>
						<?php endforeach;?>						
					</select>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<input type="submit" value="Send">
					<input type="button" value="Refresh" onclick="window.location.href='sample.php'">
				</td>
			</tr>
		</tfoot>
	</table>
</form>