<?php $wp_content_generatorPosCats = wp_content_generatorGetCategories(); ?>

<div class="wp_content_generator-success-msg" style="display: none;"></div>
<div class="wp_content_generator-error-msg" style="display: none;"></div>

<form method="post" id="wp_content_generatorGenPostForm" class="wp_content_generatorCol-9">
	<input type="hidden" name="action" value="wp_content_generatorAjaxGenPosts" />
	<input type="hidden" name="remaining_posts" class="remaining_posts" value="" />
	<input type="hidden" name="nonce" value="<?=wp_create_nonce('wpdcg-ajax-nonce')?>" />
    
    <table class="form-table">
		<tr valign="top">
	        <th scope="row">Category for API call</th>
	        <td>
	        	<select name="wp_content_generator-category">
	        		<?php foreach ($wp_content_generatorPosCats as $key => $value): ?>
	        			<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	        		<?php endforeach; ?>
	        	</select>
	        	<p class="description">Select one category to generate the data</p>
	        </td>
        </tr>
        <tr valign="top">
	        <th scope="row">Categories</th>
	        <td>
	        	<select name="wp_content_generator-categories" multiple>
	        		<?php foreach ($wp_content_generatorPosCats as $key => $value): ?>
	        			<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	        		<?php endforeach; ?>
	        	</select>
	        	<p class="description">Choose the WP post categories</p>
	        </td>
        </tr>
        <tr valign="top">
	        <th scope="row">Number of posts</th>
	        <td>
	        	<input type="number" name="wp_content_generator-post_count" class="wp_content_generator-post_count"  placeholder="Number of posts" value="1" max="500" min="1" />
	        	<p class="description">Enter the number of posts you want to generate (max 500)</p>
	        </td>
        </tr>

        <tr valign="top">
	        <th scope="row">Posts date range</th>
	        <td>
	        	<label>From</label>
	        	<input type="date" name="wp_content_generator-post_from" class="wp_content_generator-post_from"  placeholder="Date Range From" value="<?=date("Y/m/d")?>"/>

	        	<label>To</label>
	        	<input type="date" name="wp_content_generator-post_to" class="wp_content_generator-post_to"  placeholder="Date Range To" value="<?=date("Y/m/d")?>" />

	        	<p class="description">Choose the from and to date. The Plugin will pick any random date from this range to use as a post publish date</p>
	        </td>
        </tr>
        <tr valign="top" style="display: none;">
	        <th scope="row">Featured Image/Thumbnail</th>
	        <td>
	        	<input type="checkbox" name="wp_content_generator-thumbnail" />
	        	<p class="description">Check this checkbox if you want to generate the featured image for these posts</p>
	        </td>
        </tr>

		<tr valign="top" style="display: none;">
	        <th scope="row">Generate/assign terms</th>
	        <td>
	        	<input type="checkbox" name="wp_content_generator-taxonomies" /> <!-- checked -->
	        	<p class="taxonomies_wpdcg">Check this checkbox if you want to attach terms to these posts. The plugin will generate some terms and assign to these posts.</p>
	        </td>
        </tr>
    </table>

    <input class="wp_content_generator-btn btnFade wp_content_generator-btnBlueGreen wp_content_generatorGeneratePosts" type="submit" name="wp_content_generatorGeneratePosts" value="Generate posts">

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