<?php

namespace OurWorldInDataMirror;

use Html;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MWException;
use Parser;
use PPFrame;

class Hooks implements ParserFirstCallInitHook {
        /**
         * Register the &lt;ourworldindata&gt; tag
         *
         * @param Parser $parser Parser
         * @throws MWException On error
         */
        public function onParserFirstCallInit( $parser ) {
                $parser->setHook( 'ourworldindatamirror', [ $this, 'renderOurWorldInDataMirror' ] );
        }

        /**
         * Handler for the &lt;ourworldindata&gt; tag
         *
         * @param string $input Dataset to embed
         * @param array $args Arguments passed to dataset
         * @param Parser $parser Parser
         * @param PPFrame $frame Parser Frame
         * @return array HTML that will not be processed further
         */
        public function renderOurWorldInDataMirror( string $input, array $args, Parser $parser, PPFrame $frame ) {
                // check if we were given a full URL (possibly with parameters)
                $bits = wfParseUrl( $input );
                if (
                        $bits !== false
                        && $bits['host'] === 'owidm.wmcloud.org'
                        && preg_match( ',^/grapher/(.*)$,', $bits['path'], $matches )
                ) {
                        $input = $matches[1];

                        if ( isset( $bits['query'] ) ) {
                                // args passed explicitly into the tag override args present in the URL's query string
                                $args = array_merge( wfCgiToArray( $bits['query'] ), $args );
                        }
                }

                $baseUrl = 'https://owidm.wmcloud.org/grapher/' . rawurlencode( $input );
                $url = wfAppendQuery( $baseUrl, $args );
                $parser->getOutput()->addModuleStyles( [ 'ext.owid' ] );
                return [
                        Html::element(
                                'iframe',
                                [
                                        'src' => $url,
                                        'loading' => 'lazy',
                                        'class' => 'owid-frame mw-kartographer-container'
                                ]
                        ),
                        'markerType' => 'nowiki'
                ];
        }
}
