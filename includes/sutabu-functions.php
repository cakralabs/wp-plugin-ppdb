<?php

add_action( 'admin_menu', 'menu_ppdb' );

function menu_ppdb() {
 	add_menu_page(
  		'Daftar Peserta PPDB', // Judul dari halaman
  		'List Pendaftar', // Tulisan yang ditampilkan pada menu
  		'manage_options', // Persyaratan untuk dapat melihat link
  		'ppdb-daftar', // slug dari file untuk menampilkan halaman ketika menu link diklik.
  		'tampil'
 	);
}

function tampil() {
 	require_once 'daftar.php';
}

function sutabu_ppdb_type() {
	$args = array();

	register_post_type( 'sutabu_ppdb', $args );
}

add_action( 'init', 'sutabu_ppdb_type' );

function ppdb_page() {
   	$post_details = array(
  		'post_title'    => 'PPDB Page',
  		'post_content'  => 'PPDB Page',
  		'post_status'   => 'publish',
  		'post_author'   => 1,
  		'post_type' 	=> 'page'
   	);

   	$page = get_page_by_title( 'PPDB Page' );
   	if( !$page ) {
   		wp_insert_post( $post_details );
   	}
}	

add_action( 'init', 'ppdb_page' );

function form_ppdb() {
    if(is_page('ppdb-page')){   
        $dir = plugin_dir_path( __FILE__ );
        include($dir."frontend-form.php");
        die();
    }
}

add_action( 'wp', 'form_ppdb' );

add_action('init', 'register_script');
function register_script() {
    wp_register_script( 'custom_jquery', plugins_url('/js/custom-jquery.js', __FILE__), array('jquery'), '2.5.1' );
    wp_register_style( 'new_style', plugins_url('/css/new-style.css', __FILE__), false, '1.0.0', 'all');
}

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'enqueue_style');
function enqueue_style() {
   wp_enqueue_script('custom_jquery');
   wp_enqueue_style( 'new_style' );
}