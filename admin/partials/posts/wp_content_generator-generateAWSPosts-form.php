<?php $wp_content_generatorPosUser = wp_content_generatorGetUsers(); ?>
<?php $wp_content_generatorPosCategory = wp_content_generatorGetCategory(); ?>
<?php $wp_content_generatorPosCats = wp_content_generatorGetCategories(); ?>

<form method="post" id="wp_content_generatorGenAWSPostForm" class="wp_content_generatorCol-9">
	<input type="hidden" name="action" value="wp_content_generatorAjaxGenAWSPosts" />
	<input type="hidden" name="remaining_posts" class="remaining_posts" value="" />
	<input type="hidden" name="remaining_asins" class="remaining_asins" value="" />
	<input type="hidden" name="total_posts" class="total_posts" value="" />
	<input type="hidden" name="nonce" value="<?=wp_create_nonce('wpdcg-ajax-nonce')?>" />
    
    <table class="form-table">
		<tr valign="top">
	        <th scope="row">Main Category for API call</th>
	        <td>
	        	<select name="wp_content_generator-category">
	        		<?php foreach ($wp_content_generatorPosCategory as $key => $value): ?>
	        			<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	        		<?php endforeach; ?>
	        	</select>
	        	<p class="description">Select one category to generate the data</p>
	        </td>
        </tr>
        <tr valign="top">
	        <th scope="row">Categories</th>
	        <td>
	        	<select name="wp_content_generator-categories[]" multiple>
	        		<?php foreach ($wp_content_generatorPosCats as $category):
						echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
	        		endforeach; ?>
	        	</select>
	        	<p class="description">Choose the WP post categories</p>
	        </td>
        </tr>
	    <tr valign="top">
	        <th scope="row">Select the user</th>
	        <td>
	        	<select name="wp_content_generator-user">
	        		<?php foreach ($wp_content_generatorPosUser as $key => $value): ?>
	        			<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	        		<?php endforeach; ?>
	        	</select>
	        	<p class="description">Choose the user that "writes" the post</p>
	        </td>
        </tr>
        <tr valign="top">
	        <th scope="row">Posts date range</th>
	        <td>
	        	<label>From</label>
				<input type="date" name="wp_content_generator-post_from" class="wp_content_generator-post_from"  placeholder="Date Range From" value="<?=date("Y-m-d")?>" />

	        	<label>To</label>
				<input type="date" name="wp_content_generator-post_to" class="wp_content_generator-post_to"  placeholder="Date Range To" value="<?=date("Y-m-d")?>" />

	        	<p class="description">Choose the from and to date. The Plugin will pick any random date from this range to use as a post publish date</p>
	        </td>
        </tr>

		<tr valign="top"><th scope="row"><hr /></th></tr>

        <tr valign="top">
	        <th scope="row">How this section below works</th>
	        <td>
				<p>Step 1: Select the categories. It generates a post for the main category selected (the titles don't need to be created in the API before the call)</p>
				<p>Step 2: Amazon SIN list with the ASINs list. For example: B091D2CKC7 B097Y3PCTD B0C58GTXF5</p>
	        </td>
        </tr>

        <tr valign="top">
	        <th scope="row">Amazon SINs</th>
	        <td>
	        	<input type="text" name="wp_content_generator-post_asin" class="wp_content_generator-post_asin"  placeholder="ASIN1 ASIN2 ASIN3" />
	        	<p class="description">Example: B091D2CKC7 B097Y3PCTD B0C58GTXF5</p>
				<p class="description">Enter the ASINs list to call Amazon API endpoint</p>
	        </td>
        </tr>

    </table>

    <tr valign="top"><th scope="row"><hr /></th></tr>

	<input class="wp_content_generator-btn btnFade wp_content_generator-btnBlueGreen wp_content_generatorGenerateAWSPosts" type="submit" name="wp_content_generatorGenerateAWSPosts" value="Generate AWS posts">

</form>

<div class="wrapper dcsLoader wp_content_generatorCol-3" style="display: none;">
	<div class="wp_content_generatorLoaderWrpper c100 p0 blue">
		<span class="wp_content_generatorLoaderPer">0%</span>
		<div class="slice">
			<div class="bar"></div>
			<div class="fill"></div>
		</div>
	</div>
</div>