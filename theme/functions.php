<?php

Timber::$dirname = ["templates", "assets"];

if (!isset($content_width)) {
	$content_width = 1280;
}

add_action("after_setup_theme", function () {
	load_theme_textdomain("my-theme", get_theme_file_path("languages"));

	add_theme_support("automatic-feed-links");

	add_theme_support("title-tag");

	add_theme_support("post-thumbnails");

	add_theme_support("html5", [
		"comment-form",
		"comment-list",
		"gallery",
		"caption",
		"style",
		"script",
		"navigation-widgets",
	]);

	add_theme_support("customize-selective-refresh-widgets");
});

add_filter(
	"script_loader_tag",
	function ($tag, $handle, $src) {
		if (
			in_array($handle, ["theme-vite-script", "theme-main-script"], true)
		) {
			$type_attr = " type='module'";
			$tag = sprintf(
				"<script%s src='%s' id='%s-js'></script>\n",
				$type_attr,
				$src,
				esc_attr($handle)
			);
		}

		return $tag;
	},
	10,
	3
);

if (WP_DEBUG && SCRIPT_DEBUG) {
	add_action("wp_enqueue_scripts", function () {
		wp_enqueue_script(
			"theme-vite-script",
			"http://localhost:3000/@vite/client",
			[],
			null
		);

		wp_enqueue_script(
			"theme-main-script",
			"http://localhost:3000/theme/assets/main.ts",
			["theme-vite-script"],
			null
		);
	});
} else {
	add_action("wp_enqueue_scripts", function () {
		$manifest = vite_manifest();

		foreach (
			$manifest["theme/assets/main.ts"]["css"]
			as $key => $css_path
		) {
			wp_enqueue_style(
				sprintf("theme-main-%s-style", $key),
				get_theme_file_uri("build/" . $css_path),
				[],
				null
			);
		}

		wp_enqueue_script(
			"theme-main-script",
			get_theme_file_uri(
				"build/" . $manifest["theme/assets/main.ts"]["file"]
			),
			[],
			null
		);
	});
}

add_action("wp_head", function () {
	if (!($title = trim(wp_title("", false)))) {
		$title = get_bloginfo("name");
	}

	$description = get_bloginfo("description");

	if (is_single()) {
		$type = "archive";
	} else {
		$type = "website";
	}

	$image = get_theme_file_uri("assets/ogp.png");

	$site_name = get_bloginfo("name");

	$locale = get_locale();

	$url = Timber\URLHelper::get_current_url();

	$twitter_card = "summary_large_image";
	?>
	<meta name="description" content="<?= esc_attr($description) ?>">
	<meta name="twitter:card" content="<?= esc_attr($twitter_card) ?>">
	<meta property="og:title" content="<?= esc_attr($title) ?>">
	<meta property="og:type" content="<?= esc_attr($type) ?>">
	<meta property="og:image" content="<?= esc_url($image) ?>">
	<meta property="og:url" content="<?= esc_url($url) ?>">
	<meta property="og:description" content="<?= esc_attr($description) ?>">
	<meta property="og:site_name" content="<?= esc_attr($site_name) ?>">
	<meta property="og:locale" content="<?= esc_attr($locale) ?>">
	<?php
});

add_action("timber/context", function ($context) {
	$feature_post_type = new MyPostType("feature");
	$context["feature_post_type"] = $feature_post_type;

	$privacy_policy_post = Timber::get_post([
		"post_type" => "page",
		"title" => "プライバシーポリシー",
	]);

	$context["page_head_menu"] = [
		[
			"label" => $feature_post_type->label,
			"link" => $feature_post_type->archive_link(),
		],
	];

	$context["page_foot_menu"] = [
		[
			"label" => $feature_post_type->label,
			"link" => $feature_post_type->archive_link(),
		],
		[
			"label" => $privacy_policy_post->title,
			"link" => $privacy_policy_post->link,
		],
	];

	return $context;
});

class MyPostType extends Timber\PostType
{
	public function archive_link()
	{
		return get_post_type_archive_link($this->slug);
	}
}

function vite_manifest()
{
	return json_decode(
		file_get_contents(get_theme_file_path("build/manifest.json")),
		true
	);
}

require get_theme_file_path("inc/feature.php");
