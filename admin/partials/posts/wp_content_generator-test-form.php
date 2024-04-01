<div class="wp_content_generator-success-msg" style="display: none;"></div>
<div class="wp_content_generator-error-msg" style="display: none;"></div>

<form method="post" id="wp_content_generatorTestForm" class="wp_content_generatorCol-9">
	<input type="hidden" name="action" value="wp_content_generatorAjaxTest" />
	<input type="hidden" name="remaining_posts" class="remaining_posts" value="" />
	<input type="hidden" name="nonce" value="<?=wp_create_nonce('wpdcg-ajax-nonce')?>" />

    <table class="form-table">
        <tr valign="top">
	        <th scope="row">Number of posts</th>
	        <td>
	        	<input type="number" name="wp_content_generator-post_count" class="wp_content_generator-post_count"  placeholder="Number of posts" value="1" max="500" min="1" />
	        	<p class="description">Enter the number of posts you want to generate (max 500)</p>
	        </td>
        </tr>
    </table>

    <tr valign="top"><th scope="row"><hr /></th></tr>

	<input class="wp_content_generator-btn btnFade wp_content_generator-btnBlueGreen wp_content_generatorGenerateTest" type="submit" name="wp_content_generatorGenerateTest" value="Generate Test posts">

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