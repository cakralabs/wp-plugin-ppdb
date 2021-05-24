<?php
/**
 * Displays content for front page
 *
 * @package WordPress
 * @subpackage Aptrindo
 * @since 1.0
 * @version 1.0
 */

?>
<?php

class Example_List_Table extends WP_List_Table
{
    public function __construct() {

        parent::__construct(
            array(
                'singular' => 'singular_form',
                'plural'   => 'plural_form',
                'ajax'     => false
            )
        );

    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'perusahaan'  => 'Name',
            'total_ken'   => 'Total Kendaraan',
            'total_pen'   => 'Total Pengemudi',
            'tanggal'     => 'Created Date'           
        );
        return $columns;
    }

    function column_perusahaan($item)
    {
      $cek = get_user_meta($item['id'], 'aktivation', true);
      if(!$cek){
        $link = sprintf('<a href="?page=%s&action=%s&id=%s">Aktivasi</a>',$_REQUEST['page'],'aktivasi',$item['id']);
      }else{
        $link = sprintf('<a href="?page=%s&action=%s&id=%s"><font color="red">Deaktivasi</font></a>',$_REQUEST['page'],'deaktivasi',$item['id']);
      }

      $actions = array(
          'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">View</a>',$_REQUEST['page'],'view',$item['id']),
          'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
          'resend'    => sprintf('<a href="?page=%s&action=%s&id=%s">Resend</a>',$_REQUEST['page'],'resend',$item['id']),
          'manual'    => $link,
        );

      return sprintf('%1$s %2$s', $item['perusahaan'], $this->row_actions($actions) );
  }

    function get_bulk_actions() 
    {
      $actions = array(
        'delete'    => 'Delete'
      );
      
      return $actions;
  }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('name' => array('name', false));
    }
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();
        $args = array(  
            'post_type' => 'sutabu_ppdb',
            'post_status' => 'publish',
            'orderby' => 'title', 
            'order' => 'ASC', 
        );

        $the_query = new WP_Query( $args );

        if($the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();
          $data[] = array(
            'id'          => $dt->ID,
            'perusahaan'  => the_title(),
            'total_ken'   => the_content(),
            'total_pen'   => 'ok',
            'tanggal'     => 'ok'
          ); 
        endwhile; endif;
        wp_reset_postdata();

        return $data;
    }

    function column_cb($item) 
    {
        return sprintf(
            '<input type="checkbox" name="users[]" value="%s" />', $item['id']
        );    
    }
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'perusahaan':
            case 'total_ken':
            case 'total_pen':
            case 'tanggal':            
            case 'rating':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }  
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'name';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc')
        {
            return $result;
        }
        return -$result;
    }

    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $redirect = remove_query_arg(array('wp_http_referer', 'updated', 'delete_count'), wp_unslash( $_REQUEST['wp_http_referer'] ) );
        $action = $this->current_action();

        switch ( $action ) {

            case 'delete':
              $userids = $_REQUEST['users'];
              if(count($userids) > 0) {
                ?><p><?php _e( 'You have specified these users for deletion:' ); ?></p><?php
                echo "<ul>";
                foreach($userids as $dt_id) {
                  $user = get_userdata( $dt_id );
                  echo "<li><input type=\"hidden\" name=\"users[]\" value=\"" . esc_attr($dt_id) . "\" />" . sprintf(__('ID #%1$s: %2$s'), $dt_id, $user->user_login) . "</li>\n";
                }
                echo "</ul>";
              }
              do_action( 'delete_user_form', $current_user, $userids );
              ?>
              <input type="hidden" name="action" value="dodelete" />              
              <?php submit_button( __('Confirm Deletion'), 'primary' );
              wp_die();
              break;

            case 'dodelete':
                if(count($_POST['users']) > 0){
                  foreach($_POST['users'] as $dt_user){
                    wp_delete_user($dt_user);
                  }
                }
                break;

            case 'save':
                wp_die( 'Save something' );
                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }
}

if(isset($_GET['id'])){
  if($_GET['action'] == 'view'){
    ?>
    <h1 class="wp-heading-inline">Detail Perusahaan</h1>
    <div id="try-gutenberg-panel" class="try-gutenberg-panel">
      <h2 class="nav-tab-wrapper">
        <a class="nav-tab <?php echo (!isset($_GET['tab']) OR $_GET['tab'] == 'informasi')?'nav-tab-active':''; ?>" href="<?php echo admin_url( 'admin.php?page=company_list&action=view&id='.$_GET['id'].'&tab=informasi' ); ?>">Informasi Perusahaan</a>
        <a class="nav-tab <?php echo (isset($_GET['tab']) AND $_GET['tab'] == 'jam')?'nav-tab-active':''; ?>" href="<?php echo admin_url( 'admin.php?page=company_list&action=view&id='.$_GET['id'].'&tab=jam' ); ?>">Jam Kerja</a>
        <a class="nav-tab <?php echo (isset($_GET['tab']) AND $_GET['tab'] == 'legalitas')?'nav-tab-active':''; ?>" href="<?php echo admin_url( 'admin.php?page=company_list&action=view&id='.$_GET['id'].'&tab=legalitas' ); ?>">Legalitas Perusahaan</a>
        <a class="nav-tab <?php echo (isset($_GET['tab']) AND $_GET['tab'] == 'armada')?'nav-tab-active':''; ?>" href="<?php echo admin_url( 'admin.php?page=company_list&action=view&id='.$_GET['id'].'&tab=armada' ); ?>">Armada</a>
        <a class="nav-tab <?php echo (isset($_GET['tab']) AND $_GET['tab'] == 'pengemudi')?'nav-tab-active':''; ?>" href="<?php echo admin_url( 'admin.php?page=company_list&action=view&id='.$_GET['id'].'&tab=pengemudi' ); ?>">Pengemudi</a>
      </h2>   
      <div class="row">
        <?php
        if($_GET['tab'] == 'pengemudi'){
          $kueri = get_user_meta($_GET['id'], 'pengemudi', true);
          $u = 0;
          echo '<div class="toolbar"><a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=pengemudi&do=tambah" class="btn-add">Tambah Pengemudi</a></div>';
          echo "<table>";
          if($kueri){
              foreach($kueri as $key => $dt){
                foreach($dt as $d){
                  echo "<tr><td>";
                  echo "<table>";
                  $link = '<a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=pengemudi&ids='.$key.'&do=edit" class="btn-edit">Edit Pengemudi</a>&nbsp;<a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=pengemudi&ids='.$key.'&do=hapus" onclick="return confirm(\'Are you sure you want to delete this item?\');" class="btn-delete">Hapus Pengemudi</a>';
                  echo "<tr><td width='150px'>Nama</td><td>".$d['nama']." ".$link."</td></tr>";
                  $foto = ($d['foto'] != '' AND @getimagesize($d['foto']))?"<img src='".$d['foto']."'/>":"-";
                  echo "<tr><td>Foto Pengemudi</td><td>".$foto."</td></tr>";
                  echo "<tr><td>Nomor KTP</td><td>".$d['nomor']."</td></tr>";
                  $ktp = ($d['ktp'] != '' AND @getimagesize($d['ktp']))?"<img src='".$d['ktp']."'/>":"-";
                  echo "<tr><td>Foto KTP</td><td>".$ktp."</td></tr>";
                  echo "<tr><td>Alamat</td><td>".$d['alamat']."</td></tr>";
                  echo "<tr><td>Jenis Sim</td><td>".$d['jenis']."</td></tr>";
                  echo "<tr><td>Nomor Sim</td><td>".$d['nomors']."</td></tr>";
                  echo "<tr><td>Masa Berlaku</td><td>".$d['berlaku']."</td></tr>";
                  $sim = ($d['sim'] != '' AND @getimagesize($d['sim']))?"<img src='".$d['sim']."'/>":"-";
                  echo "<tr><td>Foto Sim</td><td>".$sim."</td></tr>";
                  echo "</table>";
                  echo "</td></tr>";
                  $u++;
                }
              }
          }
          
          echo "</table>";

          if($u == 0){
            echo "No Data Available";
          }
        }elseif($_GET['tab'] == 'armada'){
          $kueri = get_user_meta($_GET['id'], 'armada', true);
          $kuerik = get_user_meta($_GET['id'], 'kendaraan', true);
          $c = 0;
          echo '<div class="toolbar"><a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=armada&do=tambah" class="btn-add">Tambah Armada</a></div>';
          echo "<table>";
          if($kueri){
              foreach($kueri as $key => $dt){
                foreach($dt as $d){
                  echo "<tr><td>";
                  echo "<table>";
                  $link = '<a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=armada&ids='.$key.'&do=edit" class="btn-edit">Edit Armada</a>&nbsp;<a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=armada&ids='.$key.'&do=hapus" onclick="return confirm(\'Are you sure you want to delete this item?\');" class="btn-delete">Hapus Armada</a>';
                  $other_ken = (strtolower($d['jenis_kendaraan']) == 'other')?' ('.$d['nama_kendaraan'].')':'';
                  echo "<tr><td width='150px'>Jenis Kendaraan</td><td>".$d['jenis_kendaraan'].$other_ken." ".$link."</td></tr>";
                  $foto = ($d['foto'] != '' AND @getimagesize($d['foto']))?"<img src='".$d['foto']."'/>":"-";
                  echo "<tr><td>Foto</td><td>".$foto."</td></tr>";
                  echo "<tr><td>Silinder Kendaraan</td><td>".$d['silinder_kendaraan']." Cc</td></tr>";
                  echo "<tr><td>Kapasitas Muatan</td><td>".$d['kapasitas_muatan']." Ton</td></tr>";
                  echo "<tr><td>Panjang Karoseri</td><td>".$d['panjang_karoseri']." Cm</td></tr>";
                  echo "<tr><td>Lebar Karoseri</td><td>".$d['lebar_karoseri']." Cm</td></tr>";
                  echo "<tr><td>Tinggi Karoseri</td><td>".$d['tinggi_karoseri']." Cm</td></tr>";
                  echo "<tr><td>Volume Karoseri</td><td>".$d['volume_karoseri']." cbm</td></tr>";
                  echo "<tr><td>Detail</td><td>";
                  echo '<div class="toolbar"><a href="?page=company_list&action=edit&id='.$_GET['id'].'&tab=kendaraan&code='.$d['kode'].'&do=tambah" class="btn-add">Tambah Kendaraan</a></div>';
                  echo '<div class="table-scroll">';
                  echo "<table><tr><td>Plat Nomor</td><td>Warna</td><td>Tahun</td><td>Nomor STNK</td><td>Masa Berlaku STNK</td><td>Upload STNK STNK</td><td>Nomor BPKB</td><td>Nomor Mesin</td><td>Nomor Rangka</td><td>Nomor KIR</td><td>Masa Berlaku KIR</td><td>Upload KIR</td><td>Action</td></tr>";
                  $kuer = ($kuerik AND array_key_exists($d['kode'], $kuerik))?$kuerik[$d['kode']]:[];
                  if(count($kuer) > 0){
                    foreach($kuer as $kunci => $kr){
                      $upload_stnk = ($kr['foto'] != '' AND @getimagesize($kr['foto']))?'<a href="javascript:void(0)" onclick="window.open(\''.$kr['foto'].'\', \'\', \'width=800,height=600\');">Disini</a>':'-';
                      $upload_kir = ($kr['fkir'] != '' AND @getimagesize($kr['fkir']))?'<a href="javascript:void(0)" onclick="window.open(\''.$kr['fkir'].'\', \'\', \'width=800,height=600\');">Disini</a>':'-';
                      echo "<tr><td>".$kr['nomor']."</td><td>".$kr['warna']."</td><td>".$kr['tahun']."</td><td>".$kr['stnk']."</td><td>".$kr['masa']."</td><td>".$upload_stnk."</td><td>".$kr['bpkb']."</td><td>".$kr['mesin']."</td><td>".$kr['rangka']."</td><td>".$kr['kir']."</td><td>".$kr['berlaku']."</td><td>".$upload_kir."</td><td><a href='?page=company_list&action=edit&id=".$_GET['id']."&tab=kendaraan&ids=".$kunci."&code=".$d['kode']."&do=edit' class='btn-edit'>Edit</a>&nbsp;<a href='?page=company_list&action=edit&id=".$_GET['id']."&tab=kendaraan&ids=".$kunci."&code=".$d['kode']."&do=hapus' onclick='return confirm(\"Are you sure you want to delete this item?\");' class='btn-delete'>Hapus</a></td></tr>";
                    }
                  }else{
                    echo "<tr><td colspan='13'>No Data Available</td></tr>";
                  }
                  //print_r($kuer);
                  echo "</table>";
                  echo '</div>';
                  echo "</td></tr>";
                  echo "</table>";
                  echo "</td></tr>";
                  $c++;
                }
            }
          }
          echo "</table>";
          if($c == 0){
            echo "No Data Available";
          }
        }elseif($_GET['tab'] == 'legalitas'){
          $judul = array(
            'akte'    => 'Akte Pendirian',
            'sk'    => 'SK Menkumham',
            'izin'    => 'Izin Domisili',
            'ktp'   => 'KTP Pemimpin Perusahaan',
            'usaha'   => 'Surat Izin Usaha Perdagangan',
            'npwp'    => 'Nomor Pokok Wajib Pajak'
          );
          $kueri = get_user_meta($_GET['id'], 'perusahaan', true);
          $y = 0;
          if(count($kueri) > 0){
            ?>
            <div class="toolbar"><a href="?page=company_list&action=edit&id=<?php echo $_GET['id']; ?>&tab=legalitas" class="btn-edit">Edit</a></div>
            <table>
                <?php if($kueri): ?>
                  <?php foreach($kueri as $key => $dt): ?>
                    <tr>
                      <td width="100px"><?php echo $judul[$key]; ?></td>
                      <td>
                        <?php if($dt != '' AND file_get_contents($dt)): ?>
                            <?php $nfile = explode("/", $dt); ?>
                            <a href="<?php echo $dt; ?>" target="_blank"><?php echo end($nfile); ?></a>
                        <?php elseif($dt != '' AND @getimagesize($dt)): ?>  
                            <img src="<?php echo $dt; ?>">
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php $y++; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              <?php if($y == 0): ?>
                <?php foreach($judul as $dt_judul): ?>
                  <tr>
                    <td width="100px"><?php echo $dt_judul; ?></td>
                    <td>&nbsp;</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </table>
            <?php
          }else{
            echo "No Data Available";
          }
        }elseif($_GET['tab'] == 'jam'){
          $kueri = get_user_meta($_GET['id'], 'jam_kerja', true);
          if(count($kueri) > 0){
            ?>
            <div class="toolbar"><a href="?page=company_list&action=edit&id=<?php echo $_GET['id']; ?>&tab=jam" class="btn-edit">Edit</a></div>
            <table>
              <tr>
                <th width="150px">Hari</th>
                            <th width="150px">Status</th>
                            <th width="150px">Jam Buka</th>
                            <th width="150px">Jam Tutup</th>
                            <th width="150px">24 Jam</th>
              </tr>
              <?php if($kueri): ?>
              <?php foreach($kueri as $key => $dt): ?>
                <tr>
                  <td><?php echo ucfirst($key) ?></td>
                  <td><?php echo $dt['waktu']; ?></td>
                  <td><?php echo ($dt['waktu'] == 'Buka')?$dt['buka']:''; ?></td>
                  <td><?php echo ($dt['waktu'] == 'Buka')?$dt['tutup']:''; ?></td>
                  <td><?php echo ($dt['waktu'] == 'Buka')?($dt['full'] == 0)?'Tidak':'Ya':''; ?></td>
                </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </table>
            <?php
          }else{
            echo "No Data Available";
          }
        }else{
          $imag = get_user_meta($_GET['id'], 'logo_perusahaan', true);
          ?>
          <div class="toolbar"><a href="?page=company_list&action=edit&id=<?php echo $_GET['id']; ?>&tab=informasi" class="btn-edit">Edit</a></div>
          <table>
            <tr>
              <td width="200px">Logo Perusahaan</td>
              <td>
                <?php if($imag != '' AND @getimagesize($imag)):  ?>
                            <img src="<?php echo $imag; ?>" width="100px">
                        <?php endif; ?> 
              </td>
            </tr>
            <tr>
              <td>Nama Perusahaan</td><td><?php echo get_user_meta($_GET['id'], 'nama_perusahaan', true); ?></td>
            </tr>
            <tr>
              <td>Tahun Berdiri</td><td><?php echo get_user_meta($_GET['id'], 'tahun_perusahaan', true); ?></td>
            </tr>
            <tr>
              <td>Jumlah Karyawan</td><td><?php echo get_user_meta($_GET['id'], 'jumlah_karyawan', true); ?></td>
            </tr>
            <tr>
              <td>Deskripsi Singkat</td><td><?php echo get_user_meta($_GET['id'], 'desc_perusahaan', true); ?></td>
            </tr>
            <tr>
              <td>Standard Trading Condition</td><td><?php $stdr = get_user_meta($_GET['id'], 'standard_perusahaan', true); ?>
                <?php $stdr = get_user_meta($_GET['id'], 'standard_perusahaan', true); ?>
        <?php if($stdr != '' AND @getimagesize($stdr)): ?>  
          <img src="<?php echo $stdr; ?>">
        <?php elseif($stdr != '' AND file_get_contents($stdr)): ?>
          <?php $nstd = explode("/", $stdr); ?>
          <a href="<?php echo $stdr; ?>" target="_blank"><?php echo end($nstd); ?></a>
        <?php endif; ?>
              </td>
            </tr>
          </table>
          <?php $pst = get_user_meta($_GET['id'], 'kantor_pusat', true);; ?>
          <h2>Kantor Pusat</h2>
          <table>
            <?php
                          $directory = trailingslashit( get_template_directory_uri() );
                          $url = $directory . 'asset/json/propinsi.json';
                          $request = wp_remote_get( $url );
                          $body = wp_remote_retrieve_body( $request );
                          $data = json_decode( $body );
                      ?>
            <tr>
              <td width="200px">Provinsi</td>
              <td>
                <?php foreach($data as $prov): ?>
                  <?php if($pst AND array_key_exists('province_pusat', $pst) AND $pst['province_pusat'] == $prov->id): ?>
                    <?php echo $prov->nama; ?>
                  <?php endif; ?>
                              <?php endforeach; ?>
              </td>
            </tr>
            <?php
                          $datak = [];
                          if($pst AND array_key_exists('province_pusat', $pst)){
                            $directory = trailingslashit( get_template_directory_uri() );
                            $url = $directory . 'asset/json/kabupaten/'.$pst['province_pusat'].'.json';
                            $request = wp_remote_get( $url );
                            $body = wp_remote_retrieve_body( $request );
                            $datak = json_decode( $body );
                        }
                      ?>
            <tr>
              <td>Kabupaten</td>
              <td>
                <?php foreach($datak as $kab): ?>
                  <?php if(array_key_exists('kabupaten_pusat', $pst) AND $pst['kabupaten_pusat'] == $kab->id): ?>
                    <?php echo $kab->nama; ?>
                  <?php endif; ?>
                              <?php endforeach; ?>
              </td>
            </tr>
            <tr>
              <td>Kode Pos</td><td><?php echo ($pst AND array_key_exists('kode_pusat', $pst))?$pst['kode_pusat']:''; ?></td>
            </tr>
            <tr>
              <td>Alamat Lengkap</td><td><?php echo ($pst AND array_key_exists('alamat_pusat', $pst))?$pst['alamat_pusat']:''; ?></td>
            </tr>
            <tr>
              <td>Telepon</td><td><?php echo ($pst AND array_key_exists('telp_pusat', $pst))?$pst['telp_pusat']:''; ?></td>
            </tr>
            <tr>
              <td>Kode Fax</td><td><?php echo ($pst AND array_key_exists('fax_pusat', $pst))?$pst['fax_pusat']:''; ?></td>
            </tr>
            <tr>
              <td>Email</td><td><?php echo ($pst AND array_key_exists('email_pusat', $pst))?$pst['email_pusat']:''; ?></td>
            </tr>
            <tr>
              <td>Website</td><td><?php echo ($pst AND array_key_exists('website_pusat', $pst))?$pst['website_pusat']:''; ?></td>
            </tr>
          </table>
          <?php $cbg = get_user_meta($_GET['id'], 'kantor_cabang', true); ?>
          <h2>Kantor Cabang</h2><div class="toolbar"><a href="?page=company_list&action=edit&id=<?php echo $_GET['id']; ?>&tab=cabang&do=tambah" class="btn-add">Tambah Kantor Cabang</a></div>
          <?php       
          echo "<table><tr><td>Provinsi</td><td>Kabupaten</td><td>Kode Pos</td><td>Alamat</td><td>Telephone</td><td>Fax</td><td>Email</td><td>Action</td></tr>";
          if(count($cbg) > 0 AND $cbg){
            foreach($cbg as $key => $d_cab){
              $nama_prop = '-';
              $nama_kab = '-';
                          $directory = trailingslashit( get_template_directory_uri() );
                          $url = $directory . 'asset/json/propinsi.json';
                          $request = wp_remote_get( $url );
                          $body = wp_remote_retrieve_body( $request );
                          $data = json_decode( $body );
                          foreach($data as $prov){
                            if(array_key_exists('provinsi_cabang', $d_cab) AND $d_cab['provinsi_cabang'] == $prov->id){
                              $nama_prop = $prov->nama;
                            }                 
                          }                       
                          $datak = [];
                          if(array_key_exists('provinsi_cabang', $d_cab)){
                            $directory = trailingslashit( get_template_directory_uri() );
                            $url = $directory . 'asset/json/kabupaten/'.$d_cab['provinsi_cabang'].'.json';
                            $request = wp_remote_get( $url );
                            $body = wp_remote_retrieve_body( $request );
                            $datak = json_decode( $body );
                          }
                        foreach($datak as $kab){
                          if(array_key_exists('kabupaten_cabang', $d_cab) AND $d_cab['kabupaten_cabang'] == $kab->id){
                            $nama_kab = $kab->nama;
                          }
                        }
              echo "<tr><td>".$nama_prop."</td><td>".$nama_kab."</td><td>".$d_cab['kode_cabang']."</td><td>".$d_cab['alamat_cabang']."</td><td>".$d_cab['telp_cabang']."</td><td>".$d_cab['fax_cabang']."</td><td>".$d_cab['email_cabang']."</td><td><a href='?page=company_list&action=edit&id=".$_GET['id']."&tab=cabang&ids=".$key."&do=edit' class='btn-edit'>Edit</a>&nbsp;<a href='?page=company_list&action=edit&id=".$_GET['id']."&tab=cabang&ids=".$key."&do=hapus' onclick='return confirm(\"Are you sure you want to delete this item?\");' class='btn-delete'>Hapus</a></td></tr>";
            }
          }else{
            echo "<tr><td colspan='8'>No Data Available</td></tr>";
          }
          echo "</table>";        
        }
        ?>
      </div>
    </div>
    <?php
  }elseif($_GET['action'] == 'delete'){
    if( $_POST ) {
      if(count($_POST['users']) > 0){
        foreach ($_POST['users'] as $key => $dt) {
          wp_delete_user($dt);
        }
      }
      wp_redirect('admin.php?page=company_list');
      wp_die();
    } else {
      echo '<form id="wpse-list-table-form" method="post">';
      $userids = $_REQUEST['id'];
      // security check!    
      ?>
      <p><?php _e( 'You have specified these users for deletion:' ); ?></p>
      <ul>
        <?php
        $user = get_userdata( $userids );
        echo "<li><input type=\"hidden\" name=\"users[]\" value=\"" . esc_attr($userids) . "\" />" . sprintf(__('ID #%1$s: %2$s'), $dt_id, $user->user_login) . "</li>\n";
        ?>
      </ul>
      <?php
      do_action( 'delete_user_form', $current_user, $userids );
      ?>
      <input type="hidden" name="action" value="dodelete" />
      <?php submit_button( __('Confirm Deletion'), 'primary' );    
      echo "</form>";
      wp_die();
    }  
  }elseif($_GET['action'] == 'resend'){
    $chars = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
    srand((double)microtime() * 1000000);
    for ($i = 0; $i < 20; $i++){
      $code = $code . substr($chars, rand() % strlen($chars), 1);
    }

    $tokene = get_user_meta($_GET['id'], 'token_aktivasi', true);
    if($tokene){
      $tkne = $tokene;
    }else{
      $tkne = $code;
      add_user_meta($_GET['id'], 'token_aktivasi', $code);
    }

    $urlt = esc_url( home_url( '/aktivasi?id='.$_GET['id'].'&token='.$code ) );
    $users = get_user_by('id', $_GET['id']);

    $to = $users->user_email;
    $subject = "Registrasi Perusahaan Baru";
    $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030"><title>APTRINDO - Selamat datang</title></head><body style="margin: 0;"><table cellpadding="0" cellspacing="0" border="0" width="600" style="font-size: 16px; line-height: 30px; color: #444444; font-family: \'proximanova-regular\', sans-serif; margin: 0 auto; background: #fdfdff;"><tbody><tr><td width="100%" style="vertical-align: top; padding: 60px 30px;" align="left"><a href="#" style="display: block; font-size: 30px; line-height: 45px; margin: 0 0 50px; color: #333; font-weight: bold; text-decoration: none; ">APTRINDO</a><h1 style="font-size: 21px; line-height: 25px; margin: 0 0 20px;">Pendaftaran Berhasil</h1><p style="width: 450px; margin: 0 0 30px;">Halo '.get_user_meta($_GET['id'], 'nama_tanggungjawab', true).',<br/>terima kasih telah melakukan pendaftaran. Berikut ini adalah akses akun anda.<br/><br/>Email : '.$users->user_email.'<br>Password : '.get_user_meta($_GET['id'], 'password_user', true).'<br><br>Sebelum anda bisa menggunakannya, silahkan aktivasi akun anda dengan klik link berikut ini.<br><a href="'.$urlt.'" target="_blank">'.$urlt.'</a></p><p style="width: 450px; margin: 0 0 30px;">Jika Anda memiliki pertanyaan atau komentar, jangan ragu untuk mengirimkan pesan kepada kami di <a href="mailto:info@email.com">info@email.com</a><br/></p></td></tr><tr><td width="100%" style="vertical-align: bottom; padding: 50px 30px; font-size: 12px; color: #777;" align="left">Email ini dikirim oleh APTRINDO.</td></tr></tbody></table></body></html>';
    //$headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($to, $subject, $body);

    wp_redirect('admin.php?page=company_list');
  }elseif($_GET['action'] == 'aktivasi'){
    add_user_meta($_GET['id'], 'aktivation', 'success');
    wp_redirect('admin.php?page=company_list');
  }elseif($_GET['action'] == 'deaktivasi'){
    delete_user_meta($_GET['id'], 'aktivation');
    wp_redirect('admin.php?page=company_list');
  }
  if($_GET['action'] == 'edit'){
    if($_GET['tab'] == 'informasi'){
      get_template_part('form-admin/informasi','page');
    }elseif($_GET['tab'] == 'cabang'){
      get_template_part('form-admin/cabang','page');
    }elseif($_GET['tab'] == 'jam'){
      get_template_part('form-admin/jam','page');
    }elseif($_GET['tab'] == 'legalitas'){
      get_template_part('form-admin/legalitas','page');
    }elseif($_GET['tab'] == 'armada'){
      get_template_part('form-admin/armada','page');
    }elseif($_GET['tab'] == 'kendaraan'){
      get_template_part('form-admin/kendaraan','page');
    }elseif($_GET['tab'] == 'pengemudi'){
      get_template_part('form-admin/pengemudi','page');
    }
  }
}else{
    echo '<form id="wpse-list-table-form" method="post">';

    $page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
    $paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

    printf( '<input type="hidden" name="page" value="%s" />', $page );
    printf( '<input type="hidden" name="paged" value="%d" />', $paged );
    $exampleListTable = new Example_List_Table();
    $exampleListTable->prepare_items();
    ?>
        <div class="wrap company-list">            
            <div id="icon-users" class="icon32"></div>
            <h2>List Pendaftar</h2>
            <?php $exampleListTable->display(); ?>
        </div>
    <?php
    echo '</form>';
}
?>