<?php
require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
if(!function_exists('rbk_img')){
function rbk_img($url,$pid){ $url=html_entity_decode($url); if(!preg_match('#^https?://#',$url))return 0;
 $tmp=download_url($url,30); if(is_wp_error($tmp))return 0;
 $id=media_handle_sideload(['name'=>basename(parse_url($url,PHP_URL_PATH)),'tmp_name'=>$tmp],$pid);
 if(is_wp_error($id)){@unlink($tmp);return 0;} return $id;}
function rbk_inner($n){$s='';foreach($n->childNodes as $c)$s.=$n->ownerDocument->saveHTML($c);return $s;}
function rbk_fix($s,$home){return str_replace(['https://rbkgames.com','http://rbkgames.com'],$home,$s);}
function rbk_imgblock($aid,$alt){$url=wp_get_attachment_url($aid);return '<!-- wp:image {"id":'.$aid.',"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" class="wp-image-'.$aid.'"/></figure><!-- /wp:image -->';}
function rbk_walk($node,&$out,$home,$pid){
  foreach(iterator_to_array($node->childNodes) as $c){
   if($c->nodeType!==XML_ELEMENT_NODE){continue;}
   $tag=$c->nodeName; $html=rbk_fix(trim($c->ownerDocument->saveHTML($c)),$home);
   if($tag==='div'){rbk_walk($c,$out,$home,$pid);continue;}
   $xp=new DOMXPath($c->ownerDocument);
   $vl=$xp->query('.//a[contains(@class,"video-link")]',$c);
   if($vl->length){ $href=$vl->item(0)->getAttribute('href');
     if(strpos($href,'youtube')!==false){ $watch=preg_replace('#^.*youtube\.com/embed/([\w-]+).*$#','https://www.youtube.com/watch?v=$1',$href);
       $out[]='<!-- wp:embed {"url":"'.$watch.'","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} --><figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">'."\n".$watch."\n".'</div></figure><!-- /wp:embed -->';}
     else $out[]='<!-- wp:paragraph --><p><a href="'.esc_url($href).'" rel="nofollow">'.esc_html($href).'</a></p><!-- /wp:paragraph -->';
     continue;}
   if($tag==='p'){
     $imgs=$c->getElementsByTagName('img');
     if($imgs->length && trim($c->textContent)===''){ foreach($imgs as $img){ $aid=rbk_img($img->getAttribute('src'),$pid); if($aid)$out[]=rbk_imgblock($aid,$img->getAttribute('alt')); } continue;}
     if(trim($c->textContent)==='')continue;
     $out[]='<!-- wp:paragraph -->'.$html.'<!-- /wp:paragraph -->';continue;}
   if(preg_match('/^h([1-6])$/',$tag,$hm)){ $lvl=max(2,(int)$hm[1]); $out[]='<!-- wp:heading {"level":'.$lvl.'} --><h'.$lvl.' class="wp-block-heading">'.rbk_fix(rbk_inner($c),$home).'</h'.$lvl.'><!-- /wp:heading -->';continue;}
   if($tag==='ul'||$tag==='ol'){ $o=$tag==='ol'?' {"ordered":true}':''; $out[]='<!-- wp:list'.$o.' -->'.$html.'<!-- /wp:list -->';continue;}
   if($tag==='figure'||$tag==='img'){ foreach($c->getElementsByTagName('img') as $img){ $aid=rbk_img($img->getAttribute('src'),$pid); if($aid)$out[]=rbk_imgblock($aid,$img->getAttribute('alt'));} continue;}
   if($html!=='')$out[]='<!-- wp:html -->'.$html.'<!-- /wp:html -->';
  }}
function rbk_import($u,$home){
 $path=parse_url($u,PHP_URL_PATH); if(!preg_match('#/games/([^/]+)/news/([^/]+)/#',$path,$m))return "BADURL $u"; $gslug=$m[1];$slug=$m[2];
 $ex=get_posts(['post_type'=>'post','name'=>$slug,'post_status'=>'any','numberposts'=>1]);
 if($ex)return "EXISTS $slug";
 $r=wp_remote_get($u,['timeout'=>40]); $h=wp_remote_retrieve_body($r); if(strlen($h)<5000)return "FAIL fetch $slug";
 $g=get_posts(['post_type'=>'game','name'=>$gslug,'post_status'=>'any','numberposts'=>1]); $gid=$g?$g[0]->ID:0;
 preg_match('#<title>(.*?)</title>#s',$h,$t);$title_seo=html_entity_decode(trim($t[1]??''),ENT_QUOTES);
 preg_match('#<meta name="description" content="([^"]*)"#',$h,$d);$desc=html_entity_decode($d[1]??'',ENT_QUOTES);
 preg_match('#<h1 itemprop="headline">(.*?)</h1>#s',$h,$t);$title=html_entity_decode(trim(strip_tags($t[1]??$slug)),ENT_QUOTES);
 preg_match('#itemprop="abstract">\s*(.*?)\s*</div>#s',$h,$a);$lead=html_entity_decode(trim(strip_tags($a[1]??'')),ENT_QUOTES);
 preg_match('#article:published_time" content="([^"]+)"#',$h,$pt);$date=gmdate('Y-m-d H:i:s',strtotime($pt[1]));
 preg_match('#itemprop="image" content="([^"]+)"#',$h,$im);
 $a=strpos($h,'itemprop="articleBody"');$b=strpos($h,'<!-- Screenshots Block -->',$a);
 if($a===false||$b===false)return "FAIL body $slug";
 $body='<div>'.substr($h,strpos($h,'>',$a)+1,$b-$a).'</div>';
 $dom=new DOMDocument();libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="utf-8"?>'.$body);libxml_clear_errors();
 $root=$dom->getElementsByTagName('div')->item(0);
 $pid=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_date'=>$date,'post_date_gmt'=>$date,'post_excerpt'=>$lead,'post_category'=>[4],'comment_status'=>'closed','post_content'=>'x'],true);
 if(is_wp_error($pid))return "FAIL insert $slug";
 $out=[]; rbk_walk($root,$out,$home,$pid);
 wp_update_post(['ID'=>$pid,'post_content'=>implode("\n",$out),'post_date'=>$date,'post_date_gmt'=>$date]);
 update_post_meta($pid,'_rbk_source_path',$path);
 if($gid)update_post_meta($pid,'_rbk_game_id',$gid);
 update_post_meta($pid,'_rbk_lead',$lead);update_post_meta($pid,'_rbk_seo_title',$title_seo);update_post_meta($pid,'_rbk_seo_desc',$desc);
 if(!empty($im[1])){$tid=rbk_img($im[1],$pid);if($tid)set_post_thumbnail($pid,$tid);}
 preg_match_all('#class="tag">\s*(.*?)\s*</a>#s',substr($h,$b),$tg);if(!empty($tg[1]))wp_set_post_tags($pid,array_map(function($x){return html_entity_decode(trim($x));},$tg[1]));
 return "OK $pid $slug game=".($gid?:'MISSING:'.$gslug).' blocks='.count($out).' thumb='.(has_post_thumbnail($pid)?1:0);
}
}
