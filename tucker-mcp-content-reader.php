<?php
/**
 * Plugin Name: Tucker MCP Car Content Manager
 * Description: This contains core functionality for tuckerclug.org. It should remain activated.
 * Plugin URI: https://github.com/mattryanco/tucker-mcp-car-manager
 * Version: 2.0.0
 * Author: Matt Ryan | WDS | Claude
 * Author URI: https://mattryan.co
 * GitHub Plugin URI: https://github.com/mattryanco/tucker-mcp-car-manager
 * Primary Branch: main
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

add_action( 'wp_abilities_api_categories_init', 'tuc_register_ability_category' );

function tuc_register_ability_category() {
    wp_register_ability_category(
        'tuc',
        array(
            'label'       => 'tuc',
            'description' => 'Abilities for Tucker MCP content analysis.',
        )
    );
}

add_action( 'wp_abilities_api_init', 'tuc_register_posts_ability' );
add_action( 'wp_abilities_api_init', 'tuc_register_car_attributes_ability' );
add_action( 'wp_abilities_api_init', 'tuc_register_car_values_ability' );
add_action( 'wp_abilities_api_init', 'tuc_register_media_ability' );
add_action( 'wp_abilities_api_init', 'tuc_register_post_update_publish_ability' );
add_action( 'wp_abilities_api_init', 'tuc_register_update_car_attributes_ability' );
add_action( 'wp_abilities_api_init', 'tuc_register_car_blocks_ability' );

function tuc_register_posts_ability() {
    wp_register_ability(
        'tuc/get-posts',
        array(
            'category'    => 'tuc',
            'label'       => 'Get Car Posts',
            'description' => 'Returns published car posts with title, URL, date, excerpt, and optional direct lookup by slug or search term.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Number of posts to return. Default 50.',
                        'default'     => 50,
                    ),
                    'slug' => array(
                        'type'        => 'string',
                        'description' => 'Optional post slug to look up a single post directly.',
                    ),
                    'search' => array(
                        'type'        => 'string',
                        'description' => 'Optional keyword search to match a post by title or content.',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'posts' => array(
                        'type'  => 'array',
                        'items' => array(
                            'type'       => 'object',
                            'properties' => array(
                                'id'      => array( 'type' => 'integer' ),
                                'title'   => array( 'type' => 'string' ),
                                'url'     => array( 'type' => 'string' ),
                                'date'    => array( 'type' => 'string' ),
                                'excerpt' => array( 'type' => 'string' ),
                            ),
                        ),
                    ),
                    'total' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'tuc_get_posts_execute',
            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}
function tuc_register_car_attributes_ability() {
    wp_register_ability(
        'tuc/get-car-attributes',
        array(
            'category'    => 'tuc',
            'label'       => 'Get Car Block Attributes',
            'description' => 'Returns the Tucker Automobile Details block attributes for a named car post as a key/value map.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name' => array(
                        'type'        => 'string',
                        'description' => 'The car post name (slug) to read block attributes from.',
                    ),
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional car post ID, used when name is not provided.',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'    => array( 'type' => 'integer' ),
                    'name'       => array( 'type' => 'string' ),
                    'post_type'  => array( 'type' => 'string' ),
                    'attributes' => array(
                        'type'                 => 'object',
                        'additionalProperties' => true,
                    ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'tuc_get_car_attributes_execute',
            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function tuc_register_car_values_ability() {
    wp_register_ability(
        'tuc/get-car-values',
        array(
            'category'    => 'tuc',
            'label'       => 'Get Car Field Values',
            'description' => 'Returns the Tucker Automobile Details block values for a named car post as a key/value map.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name' => array(
                        'type'        => 'string',
                        'description' => 'The car post name (slug) to load saved field values for.',
                    ),
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional car post ID, used when name is not provided.',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'   => array( 'type' => 'integer' ),
                    'name'      => array( 'type' => 'string' ),
                    'post_type' => array( 'type' => 'string' ),
                    'values'    => array(
                        'type'                 => 'object',
                        'additionalProperties' => true,
                    ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'tuc_get_car_values_execute',
            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function tuc_register_media_ability() {
    wp_register_ability(
        'tuc/add-media',
        array(
            'category'    => 'tuc',
            'label'       => 'Add Media',
            'description' => 'Adds media to the site.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'media_url' => array(
                        'type'        => 'string',
                        'description' => 'URL of the media to add.',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'success' => array( 'type' => 'boolean' ),
                    'message' => array( 'type' => 'string' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'tuc_add_media_execute',
            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        ),
    );
}

function tuc_register_post_update_publish_ability() {
    wp_register_ability(
        'tuc/update-post-publish',
        array(
            'category'    => 'tuc',
            'label'       => 'Update Post Publish Status',
            'description' => 'Updates the publish status of a post.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'ID of the post to update.',
                    ),
                    'is_published' => array(
                        'type'        => 'boolean',
                        'description' => 'Whether the post should be published.',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'success' => array( 'type' => 'boolean' ),
                    'message' => array( 'type' => 'string' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'tuc_update_post_publish_execute',
            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        ),
    );
}

function tuc_register_update_car_attributes_ability() {
    wp_register_ability(
        'tuc/update-car-attributes',
        array(
            'category'    => 'tuc',
            'label'       => 'Update Car Block Attributes',
            'description' => 'Updates one or more attributes of the Tucker Automobile Details block on a car post (looked up by name/slug, or post_id) and saves the post. Only attributes defined by the block are accepted; unknown keys are rejected. Attributes not supplied are left unchanged. Set dry_run to preview the change without saving. Saving also removes the template Instructions block if the post still has one (attributes may be an empty object to do only that).',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name' => array(
                        'type'        => 'string',
                        'description' => 'The car post name (slug), e.g. tucker-1010.',
                    ),
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional car post ID, used when name is not provided.',
                    ),
                    'attributes' => array(
                        'type'                 => 'object',
                        'description'          => 'Block attribute key/value pairs to set, using the keys returned by tuc/get-car-attributes (e.g. currentPaintColor, onDisplay).',
                        'additionalProperties' => true,
                    ),
                    'dry_run' => array(
                        'type'        => 'boolean',
                        'description' => 'If true, return the before/after values without saving.',
                        'default'     => false,
                    ),
                ),
                'required' => array( 'attributes' ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'success'    => array( 'type' => 'boolean' ),
                    'saved'      => array( 'type' => 'boolean' ),
                    'post_id'    => array( 'type' => 'integer' ),
                    'name'       => array( 'type' => 'string' ),
                    'instructions_removed' => array(
                        'type'        => 'boolean',
                        'description' => 'True if the template Instructions block was (or, on a dry run, would be) removed.',
                    ),
                    'changed'    => array(
                        'type'                 => 'object',
                        'description'          => 'Attributes whose value changed, as key => { from, to }.',
                        'additionalProperties' => true,
                    ),
                    'attributes' => array(
                        'type'                 => 'object',
                        'description'          => 'Full set of block attribute values after the update.',
                        'additionalProperties' => true,
                    ),
                ),
            ),
            'permission_callback' => 'tuc_update_car_attributes_permission',
            'execute_callback'    => 'tuc_update_car_attributes_execute',
            'meta' => array(
                'annotations' => array(
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function tuc_get_posts_execute( $input ) {
    $input = is_array( $input ) ? $input : array();

    $per_page = isset( $input['per_page'] ) ? max( 1, intval( $input['per_page'] ) ) : 50       ;
    $slug     = isset( $input['slug'] ) ? sanitize_title( $input['slug'] ) : '';
    $search   = isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';

    $query_args = array(
        'post_type'      => 'car',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( $slug ) {
        $query_args['name'] = $slug;
    }

    if ( $search ) {
        $query_args['s'] = $search;
    }

    $query = new WP_Query( $query_args );

    $posts = array();
    foreach ( $query->posts as $post ) {
        $posts[] = array(
            'id'      => $post->ID,
            'title'   => $post->post_title,
            'url'     => get_permalink( $post->ID ),
            'date'    => get_the_date( 'Y-m-d', $post->ID ),
            'excerpt' => has_excerpt( $post->ID )
                ? get_the_excerpt( $post )
                : wp_trim_words( $post->post_content, 40 ),
        );
    }

    return array(
        'posts' => $posts,
        'total' => $query->found_posts,
    );
}

function tuc_get_car_attributes_execute( $input ) {
    $post   = tuc_get_car_post( $input );
    $values = $post ? tuc_get_car_details_values( $post ) : array();

    return array(
        'post_id'    => $post ? $post->ID : 0,
        'name'       => $post ? $post->post_name : '',
        'post_type'  => 'car',
        'attributes' => $values ? $values : new stdClass(),
    );
}

function tuc_get_car_values_execute( $input ) {
    $post   = tuc_get_car_post( $input );
    $values = $post ? tuc_get_car_details_values( $post ) : array();

    return array(
        'post_id'   => $post ? $post->ID : 0,
        'name'      => $post ? $post->post_name : '',
        'post_type' => 'car',
        'values'    => $values ? $values : new stdClass(),
    );
}

/**
 * Looks up a car post by name (slug), falling back to post_id.
 */
function tuc_get_car_post( $input ) {
    $input = is_array( $input ) ? $input : array();

    $name    = isset( $input['name'] ) ? sanitize_title( $input['name'] ) : '';
    $post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;

    $post = null;
    if ( $name ) {
        $post = get_page_by_path( $name, OBJECT, 'car' );
    } elseif ( $post_id ) {
        $post = get_post( $post_id );
    }

    return ( $post && 'car' === $post->post_type ) ? $post : null;
}

/**
 * Returns the Tucker Automobile Details block's attribute values (name => value) for a car post,
 * with the block type's registered defaults filled in for attributes not saved in the content.
 */
function tuc_get_car_details_values( $post ) {
    $block_name = 'telex/tucker-automobile-details';

    $block = tuc_find_block( parse_blocks( $post->post_content ), $block_name );
    if ( ! $block ) {
        return array();
    }

    // Start from the car detail attributes defined in block.json (all declare a default),
    // skipping the styling/support attributes WordPress adds such as className and style.
    $values     = array();
    $block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
    if ( $block_type && is_array( $block_type->attributes ) ) {
        foreach ( $block_type->attributes as $key => $schema ) {
            if ( array_key_exists( 'default', $schema ) ) {
                $values[ $key ] = $schema['default'];
            }
        }
    }

    $attrs  = (array) $block['attrs'];
    return $values ? array_merge( $values, array_intersect_key( $attrs, $values ) ) : $attrs;
}

/**
 * Finds the first block with the given name, searching inner blocks too.
 */
function tuc_find_block( $blocks, $block_name ) {
    foreach ( $blocks as $block ) {
        if ( $block_name === $block['blockName'] ) {
            return $block;
        }

        if ( ! empty( $block['innerBlocks'] ) ) {
            $found = tuc_find_block( $block['innerBlocks'], $block_name );
            if ( $found ) {
                return $found;
            }
        }
    }

    return null;
}

/**
 * Only users who can edit the target car post may update it.
 */
function tuc_update_car_attributes_permission( $input = null ) {
    $post = tuc_get_car_post( $input );
    if ( $post ) {
        return current_user_can( 'edit_post', $post->ID );
    }
    // Post not found: allow editors through so execute can return a clear "not found" error.
    return current_user_can( 'edit_posts' );
}

function tuc_update_car_attributes_execute( $input ) {
    $block_name = 'telex/tucker-automobile-details';
    $input      = is_array( $input ) ? $input : array();

    $post = tuc_get_car_post( $input );
    if ( ! $post ) {
        return new WP_Error( 'tuc_car_not_found', 'No car post found for the given name or post_id.' );
    }

    $new_attrs = isset( $input['attributes'] ) ? (array) $input['attributes'] : array();

    $block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
    if ( ! $block_type ) {
        return new WP_Error( 'tuc_block_not_registered', "The block {$block_name} is not registered." );
    }

    // Static blocks store rendered HTML alongside their attributes; changing only the
    // attributes would make the editor flag the block as invalid. Refuse rather than corrupt it.
    if ( ! $block_type->is_dynamic() ) {
        return new WP_Error( 'tuc_block_not_dynamic', "The block {$block_name} is not dynamic, so its saved markup cannot be updated safely from attributes alone." );
    }

    // Accept only the car detail attributes defined in block.json (those with defaults).
    $schema = array();
    foreach ( (array) $block_type->attributes as $key => $def ) {
        if ( array_key_exists( 'default', $def ) ) {
            $schema[ $key ] = $def;
        }
    }

    $unknown = array_diff( array_keys( $new_attrs ), array_keys( $schema ) );
    if ( $unknown ) {
        return new WP_Error(
            'tuc_unknown_attributes',
            'Unknown attribute(s): ' . implode( ', ', $unknown ) . '. Valid attributes: ' . implode( ', ', array_keys( $schema ) ) . '.'
        );
    }

    // Coerce each value to the type declared in block.json.
    $clean = array();
    foreach ( $new_attrs as $key => $value ) {
        $type = isset( $schema[ $key ]['type'] ) ? $schema[ $key ]['type'] : 'string';
        switch ( $type ) {
            case 'boolean':
                $clean[ $key ] = rest_sanitize_boolean( $value );
                break;
            case 'integer':
                $clean[ $key ] = intval( $value );
                break;
            case 'number':
                $clean[ $key ] = floatval( $value );
                break;
            default:
                $clean[ $key ] = sanitize_textarea_field( (string) $value );
        }
    }

    $blocks = parse_blocks( $post->post_content );
    $before = tuc_get_car_details_values( $post );

    if ( ! tuc_merge_block_attrs( $blocks, $block_name, $clean ) ) {
        return new WP_Error( 'tuc_block_not_found', "The post does not contain a {$block_name} block." );
    }

    $after   = array_merge( $before, $clean );
    $changed = array();
    foreach ( $clean as $key => $value ) {
        $old = isset( $before[ $key ] ) ? $before[ $key ] : null;
        if ( $old !== $value ) {
            $changed[ $key ] = array( 'from' => $old, 'to' => $value );
        }
    }

    $has_instructions = tuc_strip_instruction_blocks( $blocks )['removed'] > 0;

    $dry_run = ! empty( $input['dry_run'] );
    $saved   = false;

    // The wp_insert_post_data filter strips the instructions block on save for published posts.
    if ( ! $dry_run && ( $changed || $has_instructions ) ) {
        $result = wp_update_post(
            array(
                'ID'           => $post->ID,
                'post_content' => wp_slash( serialize_blocks( $blocks ) ),
            ),
            true
        );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        $saved = true;
        $after = tuc_get_car_details_values( get_post( $post->ID ) );
    }

    return array(
        'success'    => true,
        'saved'      => $saved,
        'instructions_removed' => $has_instructions && ( $dry_run || false === strpos( get_post_field( 'post_content', $post->ID ), 'Tucker 0000' ) ),
        'post_id'    => $post->ID,
        'name'       => $post->post_name,
        'changed'    => $changed ? $changed : new stdClass(),
        'attributes' => $after ? $after : new stdClass(),
    );
}

/**
 * Merges attributes into the first block with the given name (searching inner blocks too).
 * Returns true if a matching block was found.
 */
function tuc_merge_block_attrs( &$blocks, $block_name, $attrs ) {
    foreach ( $blocks as &$block ) {
        if ( $block_name === $block['blockName'] ) {
            $block['attrs'] = array_merge( (array) $block['attrs'], $attrs );
            return true;
        }
        if ( ! empty( $block['innerBlocks'] ) && tuc_merge_block_attrs( $block['innerBlocks'], $block_name, $attrs ) ) {
            return true;
        }
    }
    unset( $block );
    return false;
}

function tuc_add_media_execute( $input ) {
    return array(
        'success' => true,
        'message' => 'Media added successfully.',
    );
}
function tuc_update_post_publish_execute( $input ) {
    return array(
        'success' => true,
        'message' => 'Post published/updated successfully.',
    );
}

function tuc_register_car_blocks_ability() {
    wp_register_ability(
        'tuc/get-car-blocks',
        array(
            'category'    => 'tuc',
            'label'       => 'Get Car Post Blocks',
            'description' => 'Lists the top-level blocks in a car post (looked up by name/slug, or post_id) with each block name, attributes, inner block names and a plain-text preview. Read-only.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name'    => array(
                        'type'        => 'string',
                        'description' => 'The car post name (slug), e.g. tucker-1010.',
                    ),
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional car post ID, used when name is not provided.',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array( 'type' => 'integer' ),
                    'name'    => array( 'type' => 'string' ),
                    'blocks'  => array(
                        'type'  => 'array',
                        'items' => array( 'type' => 'object' ),
                    ),
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
            'execute_callback'    => 'tuc_get_car_blocks_execute',
            'meta' => array(
                'annotations' => array(
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function tuc_get_car_blocks_execute( $input ) {
    $post = tuc_get_car_post( $input );
    if ( ! $post ) {
        return new WP_Error( 'tuc_car_not_found', 'No car post found for the given name or post_id.' );
    }

    $list = array();
    foreach ( parse_blocks( $post->post_content ) as $i => $block ) {
        if ( empty( $block['blockName'] ) && '' === trim( $block['innerHTML'] ) ) {
            continue; // Whitespace between blocks.
        }
        $list[] = array(
            'index'        => $i,
            'blockName'    => $block['blockName'],
            'attrs'        => $block['attrs'] ? $block['attrs'] : new stdClass(),
            'innerBlocks'  => tuc_block_names( $block['innerBlocks'] ),
            'text'         => wp_html_excerpt( tuc_block_text( $block ), 300, '…' ),
        );
    }

    return array(
        'post_id' => $post->ID,
        'name'    => $post->post_name,
        'blocks'  => $list,
    );
}

/**
 * Plain text of a block including its inner blocks.
 */
function tuc_block_text( $block ) {
    $html = $block['innerHTML'];
    foreach ( (array) $block['innerBlocks'] as $inner ) {
        $html .= ' ' . tuc_block_text( $inner );
    }
    return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $html ) ) );
}

/**
 * Nested list of block names.
 */
function tuc_block_names( $blocks ) {
    $names = array();
    foreach ( (array) $blocks as $block ) {
        if ( empty( $block['blockName'] ) ) {
            continue;
        }
        $names[] = $block['innerBlocks']
            ? array( $block['blockName'] => tuc_block_names( $block['innerBlocks'] ) )
            : $block['blockName'];
    }
    return $names;
}

/*
 * Remove the template "Instructions" block from car posts when they are published or updated.
 * Drafts keep it so the steps stay visible while a new car post is being set up.
 */
add_filter( 'wp_insert_post_data', 'tuc_strip_instructions_on_save', 10, 2 );

function tuc_strip_instructions_on_save( $data, $postarr ) {
    if ( 'car' !== $data['post_type'] || ! in_array( $data['post_status'], array( 'publish', 'future', 'private' ), true ) ) {
        return $data;
    }

    $content = wp_unslash( $data['post_content'] );
    if ( false === strpos( $content, 'Instructions:' ) ) {
        return $data;
    }

    $result = tuc_strip_instruction_blocks( parse_blocks( $content ) );
    if ( $result['removed'] ) {
        $data['post_content'] = wp_slash( serialize_blocks( $result['blocks'] ) );
    }

    return $data;
}

/**
 * True for the template instructions block: a top-level block whose text starts with
 * "Instructions:" and refers to the 'Tucker 0000' placeholder title.
 */
function tuc_is_instructions_block( $block ) {
    if ( empty( $block['blockName'] ) ) {
        return false;
    }
    $text = tuc_block_text( $block );
    return 0 === strpos( $text, 'Instructions:' ) && false !== strpos( $text, 'Tucker 0000' );
}

/**
 * Removes top-level instruction blocks (and the blank line after each).
 * Returns array( 'blocks' => array, 'removed' => int ).
 */
function tuc_strip_instruction_blocks( $blocks ) {
    $out     = array();
    $removed = 0;
    $skip_ws = false;

    foreach ( $blocks as $block ) {
        if ( $skip_ws && empty( $block['blockName'] ) && '' === trim( $block['innerHTML'] ) ) {
            $skip_ws = false;
            continue;
        }
        $skip_ws = false;

        if ( tuc_is_instructions_block( $block ) ) {
            $removed++;
            $skip_ws = true;
            continue;
        }
        $out[] = $block;
    }

    return array( 'blocks' => $out, 'removed' => $removed );
}


/* ------------------------------------------------------------------------
 * tuc/import-live-car
 * Imports a car's details from its page on the live Tucker Club site:
 * - Car Details block attributes are set from the labeled fields (blank when missing).
 * - The block's Additional Notes (carNotes) attribute is cleared.
 * - The live "Notes" text replaces the paragraphs in the Car Content row's
 *   Show More section.
 * Saving also removes the template Instructions block (see tuc_strip_instructions_on_save).
 * --------------------------------------------------------------------- */

add_action( 'wp_abilities_api_init', 'tuc_register_import_live_car_ability' );

function tuc_register_import_live_car_ability() {
    wp_register_ability(
        'tuc/import-live-car',
        array(
            'category'    => 'tuc',
            'label'       => 'Import Car From Live Site',
            'description' => 'Reads a car page from the live Tucker Club site (https://www.tuckerclub.org/tuckers/{name}/) and updates the matching local car post: Car Details block attributes are set from the live labeled fields (blank when a field is missing), the Additional Notes attribute is cleared, and the live Notes text replaces the paragraphs in the Car Content row\'s Show More section. Saving also removes the template Instructions block. Use dry_run to preview. Returns parsed source fields, planned attributes and content, changes, and warnings for anything not imported.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name' => array(
                        'type'        => 'string',
                        'description' => 'The local car post name (slug), e.g. tucker-1001. Also used as the live page slug unless source_url is given.',
                    ),
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional local car post ID, used when name is not provided.',
                    ),
                    'source_url' => array(
                        'type'        => 'string',
                        'description' => 'Optional live page URL, when its slug differs from the local post name.',
                    ),
                    'source_html' => array(
                        'type'        => 'string',
                        'description' => 'Optional raw HTML of the live page, used instead of fetching it.',
                    ),
                    'dry_run' => array(
                        'type'        => 'boolean',
                        'description' => 'If true, return the planned changes without saving.',
                        'default'     => false,
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'success'         => array( 'type' => 'boolean' ),
                    'saved'           => array( 'type' => 'boolean' ),
                    'post_id'         => array( 'type' => 'integer' ),
                    'name'            => array( 'type' => 'string' ),
                    'source_url'      => array( 'type' => 'string' ),
                    'source_fields'   => array( 'type' => 'object', 'additionalProperties' => true ),
                    'attributes'      => array( 'type' => 'object', 'additionalProperties' => true ),
                    'changed'         => array( 'type' => 'object', 'additionalProperties' => true ),
                    'content_before'  => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
                    'content_after'   => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
                    'content_changed' => array( 'type' => 'boolean' ),
                    'instructions_removed' => array( 'type' => 'boolean' ),
                    'warnings'        => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
                ),
            ),
            'permission_callback' => 'tuc_update_car_attributes_permission',
            'execute_callback'    => 'tuc_import_live_car_execute',
            'meta' => array(
                'annotations' => array(
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function tuc_import_live_car_execute( $input ) {
    $block_name = 'telex/tucker-automobile-details';
    $input      = is_array( $input ) ? $input : array();

    $post = tuc_get_car_post( $input );
    if ( ! $post ) {
        return new WP_Error( 'tuc_car_not_found', 'No car post found for the given name or post_id.' );
    }

    // 1. Get the live page.
    $source_url = ! empty( $input['source_url'] )
        ? esc_url_raw( $input['source_url'] )
        : 'https://www.tuckerclub.org/tuckers/' . $post->post_name . '/';

    if ( ! empty( $input['source_html'] ) ) {
        $html = (string) $input['source_html'];
    } else {
        $response = wp_remote_get(
            $source_url,
            array(
                'timeout'    => 20,
                'user-agent' => 'Mozilla/5.0 (compatible; TUC car import)',
            )
        );
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'tuc_fetch_failed', 'Could not fetch ' . $source_url . ': ' . $response->get_error_message() );
        }
        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return new WP_Error( 'tuc_fetch_failed', 'Fetching ' . $source_url . ' returned HTTP ' . wp_remote_retrieve_response_code( $response ) . '.' );
        }
        $html = wp_remote_retrieve_body( $response );
    }

    // 2. Parse it.
    $parsed = tuc_parse_live_car_html( $html );
    if ( is_wp_error( $parsed ) ) {
        return $parsed;
    }
    $warnings = $parsed['warnings'];

    // 3. Map the labeled fields to block attributes (blank when missing).
    $attrs = array(
        'carNumber'             => '',
        'originalPaintCode'     => '',
        'originalPaintColor'    => '',
        'currentPaintCode'      => '',
        'currentPaintColor'     => '',
        'originalInteriorColor' => '',
        'currentInteriorColor'  => '',
        'bodyNumber'            => '',
        'originalEngineNumber'  => '',
        'currentEngineNumber'   => '',
        'transmissionType'      => '',
        'ownership'             => '',
        'location'              => '',
        'onDisplay'             => false,
        'carNotes'              => '',
    );
    $simple = array(
        'car number'              => 'carNumber',
        'original interior color' => 'originalInteriorColor',
        'current interior color'  => 'currentInteriorColor',
        'body number'             => 'bodyNumber',
        'original engine number'  => 'originalEngineNumber',
        'current engine number'   => 'currentEngineNumber',
        'transmission type'       => 'transmissionType',
        'transmission'            => 'transmissionType',
        'current transmission type' => 'transmissionType',
        'ownership'               => 'ownership',
        'owner'                   => 'ownership',
        'location'                => 'location',
    );
    $extra_paragraphs = array();

    foreach ( $parsed['fields'] as $label => $value ) {
        // A field followed by " — explanation" keeps the value; the explanation goes to Car Content.
        if ( preg_match( '/^(.*?)\s+[—–]\s+(.+)$/u', $value, $m ) && mb_strlen( $m[2] ) > 40 ) {
            $value              = trim( $m[1] );
            $extra_paragraphs[] = esc_html( trim( $m[2] ) );
            $warnings[]         = "Moved the explanation after \"{$label}\" to Car Content.";
        }

        if ( isset( $simple[ $label ] ) ) {
            $attrs[ $simple[ $label ] ] = $value;
        } elseif ( 'original paint code/color' === $label || 'original paint color' === $label ) {
            list( $attrs['originalPaintCode'], $attrs['originalPaintColor'] ) = tuc_split_paint( $value );
        } elseif ( 'current paint color' === $label || 'current paint code/color' === $label ) {
            list( $attrs['currentPaintCode'], $attrs['currentPaintColor'] ) = tuc_split_paint( $value );
        } elseif ( 'on display' === $label ) {
            $attrs['onDisplay'] = (bool) preg_match( '/^yes\b/i', $value );
            $detail             = trim( preg_replace( '/^(yes|no)\b[\s,.:;-]*/i', '', $value ) );
            if ( '' !== $detail ) {
                $warnings[] = "On Display detail not imported (yes/no field): \"{$detail}\".";
            }
        } elseif ( 'website' === $label ) {
            if ( '' !== $value ) {
                $warnings[] = "Website not imported: \"{$value}\".";
            }
        } elseif ( 'notes' !== $label ) {
            $warnings[] = "Unrecognized field not imported: \"{$label}: {$value}\".";
        }
    }

    foreach ( $attrs as $key => $value ) {
        $attrs[ $key ] = is_bool( $value ) ? $value : sanitize_text_field( $value );
    }

    $paragraphs = array_merge( $parsed['notes'], $extra_paragraphs );
    if ( ! $paragraphs ) {
        $warnings[] = 'No Notes text on the live page; the Car Content section will be empty.';
    }

    // 4. Apply to the post's blocks.
    $blocks = parse_blocks( $post->post_content );
    $before = tuc_get_car_details_values( $post );

    if ( ! tuc_merge_block_attrs( $blocks, $block_name, $attrs ) ) {
        return new WP_Error( 'tuc_block_not_found', "The post does not contain a {$block_name} block." );
    }

    $content_before = null;
    $ok             = tuc_replace_car_content_paragraphs( $blocks, $paragraphs, $content_before );
    if ( is_wp_error( $ok ) ) {
        return $ok;
    }

    $content_after   = array_map( 'tuc_paragraph_text', $paragraphs );
    $content_changed = $content_before !== $content_after;

    $changed = array();
    foreach ( $attrs as $key => $value ) {
        $old = isset( $before[ $key ] ) ? $before[ $key ] : null;
        if ( $old !== $value ) {
            $changed[ $key ] = array( 'from' => $old, 'to' => $value );
        }
    }

    $has_instructions = tuc_strip_instruction_blocks( $blocks )['removed'] > 0;
    $dry_run          = ! empty( $input['dry_run'] );
    $saved            = false;

    if ( ! $dry_run && ( $changed || $content_changed || $has_instructions ) ) {
        $result = wp_update_post(
            array(
                'ID'           => $post->ID,
                'post_content' => wp_slash( serialize_blocks( $blocks ) ),
            ),
            true
        );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        $saved = true;
    }

    return array(
        'success'              => true,
        'saved'                => $saved,
        'post_id'              => $post->ID,
        'name'                 => $post->post_name,
        'source_url'           => $source_url,
        'source_fields'        => $parsed['fields'] ? $parsed['fields'] : new stdClass(),
        'attributes'           => $attrs,
        'changed'              => $changed ? $changed : new stdClass(),
        'content_before'       => $content_before,
        'content_after'        => $content_after,
        'content_changed'      => $content_changed,
        'instructions_removed' => $has_instructions && ( $dry_run || false === strpos( get_post_field( 'post_content', $post->ID ), 'Tucker 0000' ) ),
        'warnings'             => array_values( array_unique( $warnings ) ),
    );
}

/**
 * Splits "600 Royal Maroon" into array( '600', 'Royal Maroon' ).
 * A value with no leading code ("Light Blue") returns array( '', 'Light Blue' ).
 */
function tuc_split_paint( $value ) {
    $value = trim( $value );
    if ( preg_match( '/^(\d+)\s+(.+)$/u', $value, $m ) ) {
        return array( $m[1], trim( $m[2] ) );
    }
    if ( preg_match( '/^\d+$/', $value ) ) {
        return array( $value, '' );
    }
    return array( '', $value );
}

/**
 * Parses a live car page.
 * Returns array( 'fields' => label => plain text, 'notes' => array of paragraph HTML, 'warnings' => array ),
 * with labels lower-cased and trimmed of ':' and '?'.
 */
function tuc_parse_live_car_html( $html ) {
    $doc = new DOMDocument();
    libxml_use_internal_errors( true );
    $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();

    $xpath   = new DOMXPath( $doc );
    $content = $xpath->query( "//div[contains(concat(' ', normalize-space(@class), ' '), ' entry-content ')]" )->item( 0 );
    if ( ! $content ) {
        return new WP_Error( 'tuc_parse_failed', 'Could not find the page content (.entry-content) on the live page.' );
    }

    // The data paragraph is the first <p> with a "Car Number" label.
    $data_p = null;
    foreach ( $xpath->query( './/p', $content ) as $p ) {
        if ( false !== stripos( $p->textContent, 'Car Number' ) ) {
            $data_p = $p;
            break;
        }
    }
    if ( ! $data_p ) {
        return new WP_Error( 'tuc_parse_failed', 'Could not find the car details (no "Car Number" line) on the live page.' );
    }

    // Split the data paragraph into lines at <br>; each line is <strong>Label</strong> value.
    $lines = array( array() );
    foreach ( $data_p->childNodes as $node ) {
        if ( XML_ELEMENT_NODE === $node->nodeType && 'br' === strtolower( $node->nodeName ) ) {
            $lines[] = array();
            continue;
        }
        $lines[ count( $lines ) - 1 ][] = $node;
    }

    $fields     = array();
    $notes_html = null;
    $last_label = null;

    foreach ( $lines as $nodes ) {
        // Drop leading whitespace-only text nodes.
        while ( $nodes && XML_TEXT_NODE === $nodes[0]->nodeType && '' === tuc_clean_text( $nodes[0]->nodeValue ) ) {
            array_shift( $nodes );
        }
        if ( ! $nodes ) {
            continue;
        }

        $first = $nodes[0];
        if ( XML_ELEMENT_NODE === $first->nodeType && in_array( strtolower( $first->nodeName ), array( 'strong', 'b' ), true ) ) {
            array_shift( $nodes );
            $label = strtolower( trim( tuc_clean_text( $first->textContent ), " :?\t" ) );
            if ( 'note' === $label ) {
                $label = 'notes';
            }

            if ( 'notes' === $label ) {
                $notes_html = tuc_strip_leading_colon( tuc_nodes_html( $nodes ) );
            } else {
                $fields[ $label ] = ltrim( tuc_clean_text( tuc_nodes_text( $nodes ) ), ': ' );
            }
            $last_label = $label;
        } elseif ( $last_label && 'notes' !== $last_label ) {
            // Continuation of the previous field's value.
            $fields[ $last_label ] = trim( $fields[ $last_label ] . ' ' . tuc_clean_text( tuc_nodes_text( $nodes ) ) );
        } elseif ( 'notes' === $last_label ) {
            $notes_html = trim( $notes_html . '<br>' . tuc_nodes_html( $nodes ) );
        }
    }

    // Notes continue in the paragraphs after the data paragraph, up to the gallery
    // (or any non-paragraph element). A "Website:" paragraph is reported, not imported.
    $notes    = array();
    $warnings = array();
    $has_notes_label = null !== $notes_html;

    if ( $has_notes_label && '' !== tuc_paragraph_text( $notes_html ) ) {
        $notes[] = $notes_html;
    }

    for ( $node = $data_p->nextSibling; $node; $node = $node->nextSibling ) {
        if ( XML_TEXT_NODE === $node->nodeType ) {
            if ( '' === tuc_clean_text( $node->nodeValue ) ) {
                continue;
            }
            break;
        }
        if ( XML_ELEMENT_NODE !== $node->nodeType ) {
            continue;
        }
        if ( 'p' !== strtolower( $node->nodeName ) ) {
            break;
        }

        // Before the Notes label: known "Label: value" lines are fields, a "Notes:"/"Note:"
        // label starts the Notes section, anything else is reported. After it: lines are
        // Notes text. "Website:" lines are reported as a field in either case.
        $kept    = array();
        $outside = array();
        foreach ( tuc_split_lines( $node ) as $line ) {
            $label = tuc_line_label( $line );

            if ( null !== $label && preg_match( '/^website\b/', $label ) ) {
                array_shift( $line );
                $fields['website'] = ltrim( tuc_clean_text( tuc_nodes_text( $line ) ), ': ' );
                continue;
            }

            if ( ! $has_notes_label ) {
                if ( 'notes' === $label || 'note' === $label ) {
                    $has_notes_label = true;
                    array_shift( $line );
                    $rest = tuc_strip_leading_colon( tuc_nodes_html( $line ) );
                    if ( '' !== tuc_paragraph_text( $rest ) ) {
                        $kept[] = $rest;
                    }
                    continue;
                }
                if ( null !== $label && in_array( $label, tuc_known_field_labels(), true ) ) {
                    array_shift( $line );
                    $fields[ $label ] = ltrim( tuc_clean_text( tuc_nodes_text( $line ) ), ': ' );
                    continue;
                }
                $text = tuc_paragraph_text( tuc_nodes_html( $line ) );
                if ( '' !== $text ) {
                    $outside[] = $text;
                }
                continue;
            }

            $line_html = tuc_nodes_html( $line );
            if ( '' !== tuc_paragraph_text( $line_html ) ) {
                $kept[] = $line_html;
            }
        }

        if ( $outside ) {
            $warnings[] = 'Text outside a Notes section not imported: "' . implode( ' ', $outside ) . '"';
        }
        if ( $kept ) {
            $notes[] = implode( '<br>', $kept );
        }
    }

    return array(
        'fields'   => $fields,
        'notes'    => array_values( array_filter( array_map( 'trim', $notes ), 'strlen' ) ),
        'warnings' => $warnings,
    );
}

/**
 * Splits an element's children into lines at <br>, dropping leading whitespace
 * and empty lines. Returns an array of node arrays.
 */
function tuc_split_lines( $element ) {
    $lines = array( array() );
    foreach ( $element->childNodes as $node ) {
        if ( XML_ELEMENT_NODE === $node->nodeType && 'br' === strtolower( $node->nodeName ) ) {
            $lines[] = array();
            continue;
        }
        $lines[ count( $lines ) - 1 ][] = $node;
    }
    $out = array();
    foreach ( $lines as $nodes ) {
        while ( $nodes && XML_TEXT_NODE === $nodes[0]->nodeType && '' === tuc_clean_text( $nodes[0]->nodeValue ) ) {
            array_shift( $nodes );
        }
        if ( $nodes ) {
            $out[] = $nodes;
        }
    }
    return $out;
}

/** Lower-cased label of a line that starts with <strong>/<b>, or null. */
function tuc_line_label( $line ) {
    $first = $line[0];
    if ( XML_ELEMENT_NODE === $first->nodeType && in_array( strtolower( $first->nodeName ), array( 'strong', 'b' ), true ) ) {
        return strtolower( trim( tuc_clean_text( $first->textContent ), " :?\t" ) );
    }
    return null;
}

/** Labels of the car detail fields the importer maps. */
function tuc_known_field_labels() {
    return array(
        'car number', 'original paint code/color', 'original paint color', 'current paint color',
        'current paint code/color', 'original interior color', 'current interior color', 'body number',
        'original engine number', 'current engine number', 'transmission type', 'transmission',
        'current transmission type', 'original transmission type', 'ownership', 'owner', 'location', 'on display',
    );
}

/** Normalizes whitespace (including non-breaking spaces) in plain text. */
function tuc_clean_text( $text ) {
    $text = str_replace( "\xC2\xA0", ' ', (string) $text );
    return trim( preg_replace( '/\s+/u', ' ', $text ) );
}

/** Plain text of a list of DOM nodes. */
function tuc_nodes_text( $nodes ) {
    $text = '';
    foreach ( $nodes as $node ) {
        $text .= $node->textContent;
    }
    return $text;
}

/**
 * Safe inline HTML for a list of DOM nodes: keeps strong/b/em/i/br and links (href only),
 * unwraps any other element, and escapes text.
 */
function tuc_nodes_html( $nodes, $trim = true ) {
    $html = '';
    foreach ( $nodes as $node ) {
        if ( XML_TEXT_NODE === $node->nodeType ) {
            $html .= esc_html( preg_replace( '/\s+/u', ' ', str_replace( "\xC2\xA0", ' ', $node->nodeValue ) ) );
        } elseif ( XML_ELEMENT_NODE === $node->nodeType ) {
            $tag      = strtolower( $node->nodeName );
            $children = tuc_nodes_html( iterator_to_array( $node->childNodes ), false );
            if ( 'br' === $tag ) {
                $html .= '<br>';
            } elseif ( in_array( $tag, array( 'strong', 'b', 'em', 'i' ), true ) ) {
                $html .= "<{$tag}>{$children}</{$tag}>";
            } elseif ( 'a' === $tag && $node->getAttribute( 'href' ) ) {
                $html .= '<a href="' . esc_url( $node->getAttribute( 'href' ) ) . '">' . $children . '</a>';
            } else {
                $html .= $children;
            }
        }
    }
    return $trim ? trim( $html ) : $html;
}

/** Removes a ":" (and spaces) left at the start of the Notes value. */
function tuc_strip_leading_colon( $html ) {
    return trim( preg_replace( '/^\s*:\s*/u', '', $html ) );
}

/** Plain text of a paragraph's HTML. */
function tuc_paragraph_text( $html ) {
    return tuc_clean_text( html_entity_decode( wp_strip_all_tags( str_replace( '<br>', ' ', $html ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Replaces the paragraphs in the Car Content row's Show More section
 * (the first section inside the kadence/show-more block) with $paragraphs (inline HTML strings).
 * $content_before receives the plain text of the paragraphs that were there.
 */
function tuc_replace_car_content_paragraphs( &$blocks, $paragraphs, &$content_before ) {
    foreach ( $blocks as &$row ) {
        $name = isset( $row['attrs']['metadata']['name'] ) ? $row['attrs']['metadata']['name'] : '';
        if ( 'kadence/rowlayout' !== $row['blockName'] || 'Car Content' !== $name ) {
            continue;
        }

        $section = &tuc_find_show_more_section( $row['innerBlocks'] );
        if ( null === $section ) {
            return new WP_Error( 'tuc_section_not_found', 'The Car Content row has no Show More section.' );
        }

        $content_before = array();
        foreach ( $section['innerBlocks'] as $inner ) {
            if ( 'core/paragraph' === $inner['blockName'] ) {
                $content_before[] = tuc_paragraph_text( $inner['innerHTML'] );
            }
        }

        $new_inner = array();
        foreach ( $paragraphs as $p ) {
            $markup      = "\n<p>{$p}</p>\n";
            $new_inner[] = array(
                'blockName'    => 'core/paragraph',
                'attrs'        => array(),
                'innerBlocks'  => array(),
                'innerHTML'    => $markup,
                'innerContent' => array( $markup ),
            );
        }

        return tuc_set_inner_blocks( $section, $new_inner );
    }
    unset( $row );

    return new WP_Error( 'tuc_row_not_found', 'The post has no "Car Content" row.' );
}

/** Returns a reference to the first section (kadence/column) inside the first kadence/show-more block. */
function &tuc_find_show_more_section( &$blocks ) {
    $null = null;
    foreach ( $blocks as &$block ) {
        if ( 'kadence/show-more' === $block['blockName'] ) {
            foreach ( $block['innerBlocks'] as &$inner ) {
                if ( 'kadence/column' === $inner['blockName'] ) {
                    return $inner;
                }
            }
            return $null;
        }
        if ( ! empty( $block['innerBlocks'] ) ) {
            $found = &tuc_find_show_more_section( $block['innerBlocks'] );
            if ( null !== $found ) {
                return $found;
            }
        }
    }
    return $null;
}

/**
 * Replaces a block's inner blocks, rebuilding innerContent so the wrapper markup
 * (the text before the first and after the last inner block) is kept.
 */
function tuc_set_inner_blocks( &$block, $new_inner ) {
    $ic = array_values( (array) $block['innerContent'] );

    if ( in_array( null, $ic, true ) ) {
        $open  = is_string( $ic[0] ) ? $ic[0] : '';
        $last  = $ic[ count( $ic ) - 1 ];
        $close = is_string( $last ) ? $last : '';
    } elseif ( 2 === count( $ic ) ) {
        // An emptied section saved by this importer: array( open, close ).
        list( $open, $close ) = $ic;
    } elseif ( 1 === count( $ic ) && is_string( $ic[0] ) ) {
        // An empty section saved by the editor: one string. Inner blocks go inside the
        // innermost wrapper, i.e. before the second-to-last closing </div>.
        $html = $ic[0];
        $last = strrpos( $html, '</div>' );
        $pos  = false !== $last ? strrpos( substr( $html, 0, $last ), '</div>' ) : false;
        if ( false === $pos ) {
            return new WP_Error( 'tuc_section_unexpected', 'The Show More section has an unexpected structure.' );
        }
        $open  = substr( $html, 0, $pos );
        $close = substr( $html, $pos );
    } else {
        return new WP_Error( 'tuc_section_unexpected', 'The Show More section has an unexpected structure.' );
    }

    $sep = "\n\n";
    for ( $i = 1; $i < count( $ic ) - 1; $i++ ) {
        if ( is_string( $ic[ $i ] ) && null === $ic[ $i - 1 ] && null === $ic[ $i + 1 ] ) {
            $sep = $ic[ $i ];
            break;
        }
    }

    $new_ic = array( $open );
    foreach ( $new_inner as $i => $unused ) {
        if ( $i ) {
            $new_ic[] = $sep;
        }
        $new_ic[] = null;
    }
    $new_ic[] = $close;

    $block['innerBlocks']  = $new_inner;
    $block['innerContent'] = $new_ic;
    $block['innerHTML']    = $open . $close;

    return true;
}


/* ------------------------------------------------------------------------
 * tuc/import-live-cars
 * Runs tuc/import-live-car over several cars and returns a compact summary.
 * --------------------------------------------------------------------- */

add_action( 'wp_abilities_api_init', 'tuc_register_import_live_cars_ability' );

function tuc_register_import_live_cars_ability() {
    wp_register_ability(
        'tuc/import-live-cars',
        array(
            'category'    => 'tuc',
            'label'       => 'Import Cars From Live Site (Batch)',
            'description' => 'Runs tuc/import-live-car for up to 15 car posts and returns a compact per-car summary (changed attributes with new values, Car Content paragraph count and preview, warnings, errors). Pass names (slugs), or omit names and use offset/limit to page through all car posts in title order. Use dry_run to preview. For a car whose live slug differs from its local slug, pass sources: { local-slug: live-url }.',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'names' => array(
                        'type'        => 'array',
                        'items'       => array( 'type' => 'string' ),
                        'description' => 'Local car post slugs to import, e.g. ["tucker-1011","tucker-1012"]. Max 15.',
                    ),
                    'offset' => array(
                        'type'        => 'integer',
                        'description' => 'When names is omitted: number of car posts (sorted by title) to skip.',
                        'default'     => 0,
                    ),
                    'limit' => array(
                        'type'        => 'integer',
                        'description' => 'When names is omitted: number of car posts to process (max 15).',
                        'default'     => 10,
                    ),
                    'sources' => array(
                        'type'                 => 'object',
                        'description'          => 'Optional map of local slug => live page URL, for cars whose live slug differs.',
                        'additionalProperties' => array( 'type' => 'string' ),
                    ),
                    'dry_run' => array(
                        'type'        => 'boolean',
                        'description' => 'If true, report the planned changes without saving.',
                        'default'     => true,
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'dry_run'     => array( 'type' => 'boolean' ),
                    'total_cars'  => array( 'type' => 'integer' ),
                    'next_offset' => array( 'type' => array( 'integer', 'null' ) ),
                    'results'     => array( 'type' => 'array', 'items' => array( 'type' => 'object' ) ),
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
            'execute_callback'    => 'tuc_import_live_cars_execute',
            'meta' => array(
                'annotations' => array(
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ),
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function tuc_import_live_cars_execute( $input ) {
    $input   = is_array( $input ) ? $input : array();
    $dry_run = array_key_exists( 'dry_run', $input ) ? (bool) $input['dry_run'] : true;
    $sources = isset( $input['sources'] ) ? (array) $input['sources'] : array();

    $all = get_posts(
        array(
            'post_type'      => 'car',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        )
    );

    $next_offset = null;
    if ( ! empty( $input['names'] ) ) {
        $names = array_slice( array_map( 'sanitize_title', (array) $input['names'] ), 0, 15 );
    } else {
        $offset = isset( $input['offset'] ) ? max( 0, intval( $input['offset'] ) ) : 0;
        $limit  = isset( $input['limit'] ) ? min( 15, max( 1, intval( $input['limit'] ) ) ) : 10;
        $ids    = array_slice( $all, $offset, $limit );
        $names  = array_map(
            function ( $id ) {
                return get_post_field( 'post_name', $id );
            },
            $ids
        );
        $next_offset = ( $offset + $limit < count( $all ) ) ? $offset + $limit : null;
    }

    if ( function_exists( 'set_time_limit' ) ) {
        @set_time_limit( 30 + 20 * count( $names ) );
    }

    $results = array();
    foreach ( $names as $name ) {
        $args = array( 'name' => $name, 'dry_run' => $dry_run );
        if ( ! empty( $sources[ $name ] ) ) {
            $args['source_url'] = $sources[ $name ];
        }

        $r = tuc_import_live_car_execute( $args );

        if ( is_wp_error( $r ) ) {
            $results[] = array(
                'name'  => $name,
                'error' => $r->get_error_message(),
            );
            continue;
        }

        $changed = array();
        foreach ( $r['changed'] as $key => $diff ) {
            $changed[ $key ] = $diff['to'];
        }

        $after   = $r['content_after'];
        $results[] = array(
            'name'                 => $name,
            'post_id'              => $r['post_id'],
            'saved'                => $r['saved'],
            'changed'              => $changed ? $changed : new stdClass(),
            'content_changed'      => $r['content_changed'],
            'content_paragraphs'   => count( $after ),
            'content_preview'      => $after ? wp_html_excerpt( implode( ' ', $after ), 120, '…' ) : '',
            'instructions_removed' => $r['instructions_removed'],
            'warnings'             => $r['warnings'],
        );
    }

    return array(
        'dry_run'     => $dry_run,
        'total_cars'  => count( $all ),
        'next_offset' => $next_offset,
        'results'     => $results,
    );
}
