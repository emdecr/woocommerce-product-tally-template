<?php 
/**
* Template Name: Tally
*
*/
if (isset( $_POST['submitted'] )) {

    $cat = $_POST['cats'];
    $cat_id = intval($cat);
    $from = isset( $_POST['from'] ) ? $_POST['from'] : '';
    $to = isset( $_POST['to'] ) ? $_POST['to'] : '';
    
    wp_redirect( get_permalink().'?cat='.$cat_id.'&from='.$from.'&to='.$to );
   
    exit;    
} 

$cats = get_terms( array( 
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
) );

get_header(); ?>

<main>

<?php if ( current_user_can( 'edit_posts' ) ) { ?>

<section  style="padding: 2rem 2rem 0;">
	<h1>Product Tally</h1>
</section>

<section  style="padding: 0 2rem;">
    <form action="" method="POST">
        <label for="cats">Event Category:</label>
        <select name="cats" id="cats">
        <?php 
        foreach ( $cats as $cat ) {
            if (isset($_GET['cat'])) {
                if ($_GET['cat'] == $cat->term_id) {
                    echo '<option value="'.$cat->term_id. '" selected="selected">' . $cat->name . '</option>';
                } else {
                    echo '<option value="'.$cat->term_id. '">' . $cat->name . '</option>';
                }
            } else {
                echo '<option value="'.$cat->term_id. '">' . $cat->name . '</option>';
            }
        } ?>
        </select>
        <br>
        <span><strong>Date Range (optional):</strong></span>
        <br> 
        <style>
        @media only screen and (min-width: 500px) {
            .date-range {
                display: flex;
            }
            .date-range >* {
                width: 30%;
            }
            .date-range >*:first-child {
                margin-right: 20px;
            }
        }
        </style>
        <div class="date-range">
            <div>
                <label for="from">From</label>
                <input type="text" id="from" name="from" <?php if (isset($_GET['from'])) { echo 'value="'.$_GET['from'].'"';}?>>
            </div>
            <div>
                <label for="to">To</label>
                <input type="text" id="to" name="to" <?php if (isset($_GET['to'])) { echo 'value="'.$_GET['to'].'"';}?>>
            </div>
        </div>
        <input type="Submit" name="submitted" style="background: rgb(33, 33, 33); border-radius: 5px;width: 100px;color: #fff;">
        <br>
        <br>
    </form>
</section>
<?php }  else { ?>
<section>
    <p>Please log in to see this page.</p>
</section>
<?php } ?> 

<?php if ( current_user_can( 'edit_posts' ) ) { 

if (isset($_GET['cat'])) {

    $cat_id = intval($_GET['cat']); 

    global $wpdb;
    // Get all the product_ids of product w/ a specific term_id
    $query_1="SELECT object_id #3
    FROM wp_term_relationships
    WHERE term_taxonomy_id=$cat_id";

    $result_1 = $wpdb->get_results($query_1);

    $master_array = array();

    function getDates($from, $to){
        $dateF = date_create($from);
        $F = date_format($dateF,"Y-m-d");
        $FStr = $F.' 00:00:00';
        $dateT = date_create($to);
        $T = date_format($dateT,"Y-m-d");
        $TStr = $T.' 23:59:59';

        $obj = new stdClass();
        $obj->from = $FStr;
        $obj->to = $TStr;

        return $obj;
    }

    function getQuery($id, $var, $from, $to) {
        if ($var != '') {
            if ($from != '') {
                $query_string = "SELECT SUM(product_qty) as sum
                FROM wp_wc_order_product_lookup
                WHERE product_id = $id 
                AND variation_id = $var
                AND product_qty > 0
                AND date_created >= '$from'
                AND date_created <= '$to'
                AND order_id IN (SELECT order_id
                                FROM wp_wc_order_stats
                                WHERE status='wc-completed')";
            } else {
                $query_string = "SELECT SUM(product_qty) as sum
                FROM wp_wc_order_product_lookup
                WHERE product_id = $id 
                AND variation_id = $var
                AND product_qty > 0
                AND order_id IN (SELECT order_id
                                FROM wp_wc_order_stats
                                WHERE status='wc-completed')";
            }
        } else {
            if ($from != '') {
                $query_string = "SELECT DISTINCT variation_id as var
                FROM wp_wc_order_product_lookup
                WHERE product_id = $id
                AND variation_id <> 0
                AND date_created >= '$from'
                AND date_created <= '$to'
                AND order_id IN (SELECT order_id
                                FROM wp_wc_order_stats
                                WHERE status='wc-completed')";
            } else {
                $query_string = "SELECT DISTINCT variation_id as var
                FROM wp_wc_order_product_lookup
                WHERE product_id = $id
                AND variation_id <> 0
                AND order_id IN (SELECT order_id
                                FROM wp_wc_order_stats
                                WHERE status='wc-completed')";
            }
        }
        return $query_string;
    }

    function getSum($id, $from, $to) {
        if ($from != '') {
            $query_string = "SELECT SUM(product_qty) as sum
            FROM wp_wc_order_product_lookup
            WHERE product_id = $id
            AND variation_id = 0
            AND product_qty > 0
            AND date_created >= '$from'
            AND date_created <= '$to'
            AND order_id IN (SELECT order_id
                                FROM wp_wc_order_stats
                                WHERE status='wc-completed')";
        } else {
            $query_string = "SELECT SUM(product_qty) as sum
            FROM wp_wc_order_product_lookup
            WHERE product_id = $id
            AND variation_id = 0
            AND product_qty > 0
            AND order_id IN (SELECT order_id
                                FROM wp_wc_order_stats
                                WHERE status='wc-completed')";
        }
        return $query_string;
    }

    foreach ( $result_1 as $key=>$value ) {

        $name = get_the_title( $value->object_id );
        $id = intval($value->object_id); 

        if ( $_GET['from'] != '' && $_GET['to'] != '' ) { 
            $from = $_GET['from'];
            $to = $_GET['to'];
            $dates = getDates($from, $to);
            $parent_query = getQuery($id, '', $dates->from, $dates->to);
            $parent_sum_query = getSum($id, $dates->from, $dates->to);
        } else if ( $_GET['from'] != '' && $_GET['to'] == '' ) {
            $from = $_GET['from'];
            $to = date("Y-m-d");
            $dates = getDates($from, $to);
            $parent_query = getQuery($id, '', $dates->from, $dates->to);
            $parent_sum_query = getSum($id, $dates->from, $dates->to);
        } else if ($_GET['from'] == '' && $_GET['to'] != '') {
            $from = '01/01/1980';
            $to = $_GET['to'];
            $dates = getDates($from, $to);
            $parent_query = getQuery($id, '', $dates->from, $dates->to);
            $parent_sum_query = getSum($id, $dates->from, $dates->to);
        } else {
            $parent_query = getQuery($id, '', '', '');
            $parent_sum_query = getSum($id, $dates->from, $dates->to);
        }

        $parent_res = $wpdb->get_results($parent_query);
        $parent_sum_res = $wpdb->get_results($parent_sum_query);
        $parent_sum = $parent_sum_res[0]->sum;

        $obj = new stdClass();
        $obj->id = $value->object_id;
        $obj->name = $name;
        $obj->count = $parent_sum; 

        if (count($parent_res) > 0) {
            $array = array();

            foreach ( $parent_res as $key=>$value ) {
                $name2 = get_the_title( $value->var );
                if ( $_GET['from'] != '' && $_GET['to'] != '' ) { 
                    $from = $_GET['from'];
                    $to = $_GET['to'];
                    $dates = getDates($from, $to);
                    $child_query = getQuery($id, $value->var, $dates->from, $dates->to);
                } else if ($_GET['from'] != '' && $_GET['to'] == '') {
                    $from = $_GET['from'];
                    $to = date("Y-m-d");
                    $dates = getDates($from, $to);
                    $child_query = getQuery($id, $value->var, $dates->from, $dates->to);
                } else if ($_GET['from'] == '' && $_GET['to'] != '') {
                    $from = '01/01/1980';
                    $to = $_GET['to'];
                    $dates = getDates($from, $to);
                    $child_query = getQuery($id, $value->var, $dates->from, $dates->to);
                }  else {
                    $child_query = getQuery($id, $value->var, '', '');
                }

                $child_res = $wpdb->get_results($child_query);
                $obj2 = new stdClass();
                $obj2->id = $value->var;
                $obj2->name = $name2;
                $obj2->count = $child_res[0]->sum;
                array_push($array, $obj2);
            }
            $obj->var = $array;
        } else {
            $obj->var = array();
        }
        array_push($master_array, $obj); 
    }
    
}
?>

<? if (isset($_GET['cat'])) { ?>
<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;border: 1px solid black;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-0pky.c{text-align: center;}
.tg .tg-0pky.alternate{background: lightgrey;}
@media only screen and (min-width: 500px) {
    #event_table {
        width: 60%;
    }
}
</style>
<section style="padding: 0 2%;">
    <table id="event_table"class="widefat fixed tg" cellspacing="0" >
        <tr>
            <th class="tg-0pky" style="width: 90%;"><strong>Product</strong></th>
            <th class="tg-0pky c"><strong>Count</strong><br></th>
        </tr>
        <?php foreach ( $master_array as $key=>$value ) {
            ?>
        <tr>
            <td class="tg-0pky" style="background:#efefef;"><?php echo($value->name); ?></td>
            <td class="tg-0pky c" style="background:#efefef;">
            <?php if (count($value->var) > 0) {
                $sum = 0;
                $result=$value->var;
                foreach($result as $k2=>$v2){
                
                if(isset($v2->count))   
                    $sum += $v2->count;
                }
                echo $sum;
            } else {
                echo $value->count == 0 ? '0' : $value->count;
            } ?>
            </td>
        </tr>

        <?php  if (count($value->var) > 0) { ?>
        
        <?php foreach ( $value->var as $k=>$v ) { ?>
            <tr>
            <td class="tg-0pky" style="background:#fff;padding-left:20px;"><?php echo($v->name); ?></td>
            <td class="tg-0pky c" style="background:#fff;"><?php echo($v->count); ?></td>
            </tr>
        <?php } ?>
        
        <?php } ?>

        <?php } ?>
    </table>
</section>
<?php } ?> 
<?php } ?> 

</main>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $ = jQuery;
  $( function() {
    var dateFormat = "mm/dd/yy",
      from = $( "#from" )
        .datepicker({
          changeMonth: true,
          numberOfMonths: 2
        })
        .on( "change", function() {
          to.datepicker( "option", "minDate", getDate( this ) );
        }),
      to = $( "#to" ).datepicker({
        changeMonth: true,
        numberOfMonths: 2
      })
      .on( "change", function() {
        from.datepicker( "option", "maxDate", getDate( this ) );
      });
 
    function getDate( element ) {
      var date;
      try {
        date = $.datepicker.parseDate( dateFormat, element.value );
      } catch( error ) {
        date = null;
      }
 
      return date;
    }
  } );
  </script>

<?php get_footer(); ?>