<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Defines a proper embeddable rules for vc_video (in future may be used for custom usages as well)
 *
 * Note: in 'html' field '<id>' entry will be replaced by match_index's entrance from regex matches.
 *
 * @filter us_config_embeds
 */

return array(
	// @see https://developers.google.com/youtube/player_parameters?hl=en#IFrame_Player_API
	'youtube' => array(
		'player_api' => '//www.youtube.com/player_api',
		'player_vars' => array( // https://developers.google.com/youtube/player_parameters?hl=en#Parameters
			'autoplay' => 0,
			'controls' => 1,
		),
		'player_html' => '<div id="<player_id>"></div><script>
			if ( window.USYTPlayers === undefined ) {
				window.USYTPlayers = [];
			}
			window.USYTPlayers.push( {
				playerID: "<player_id>",
				videoID: "<video_id>",
				playerVars: <player_vars>
			} );
			if ( window.USYTInited === undefined ) {
				// Trying to override other possible WP YT API calls if our element is used
				window.USYTInited = true;
				window.onYouTubePlayerAPIReady = function() {
					for ( var i in window.USYTPlayers ) {
						var _playerParams = window.USYTPlayers[ i ];
						$us.YTPlayers[ _playerParams.playerID ] = new YT.Player( _playerParams.playerID, {
							videoId: _playerParams.videoID,
							playerVars: _playerParams.playerVars
						} );
					}
				}
			}
			if ( !! window.YT ) {
				$us.YTPlayers["<player_id>"] = new YT.Player( "<player_id>", {
					videoId: "<video_id>",
					playerVars: <player_vars>
				} );
			}
		</script>',
		'url_regex' => '~^(?:https?://)?(?:www\.|m\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$~x',
		// GDPR compliance: src="//www.youtube-nocookie.com/embed/<video_id>?<player_url_params>"
		'iframe_html' => '<iframe title="Youtube video player" src="//www.youtube.com/embed/<video_id>?<player_url_params>" allowfullscreen="1" loading="lazy"></iframe>',
	),
	'vimeo' => array(
		'get_video_privacy' => function ( $url ) {
			return preg_match( '/^http(?:s)?:\/\/(?:.*?)\.?vimeo\.com\/(\d+)\/([a-z0-9]+).*$/i', (string) $url, $matches )
				? $matches[ /* Privacy hash */ 2]
				: '';
		},
		'player_vars' => array( // https://developer.vimeo.com/player/sdk/embed
			'autoplay' => 0,
			'loop' => 1,
			'autopause' => 0,
			'color' => '00adef',
			'portrait' => 0,
		),
		// Note: Without a `https` protocol, autoplay will not work.
		'player_html' => '<iframe title="Vimeo video player" src="https://player.vimeo.com/video/<video_id>?<player_url_params>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe>',
		'url_regex' => '/^http(?:s)?:\/\/(?:.*?)\.?vimeo\.com\/(\d+).*$/i',
		'iframe_html' => '<iframe title="Vimeo video player" src="https://player.vimeo.com/video/<video_id>?<player_url_params>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe>',
	),
);
