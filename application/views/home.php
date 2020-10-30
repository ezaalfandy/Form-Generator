<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets')?>/img/apple-icon.png">
  <link rel="icon" type="image/png" href="<?= base_url('assets')?>/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    Form Generator
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
    name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <!-- CSS Files -->
  <link href="<?= base_url('assets')?>/css/bootstrap.min.css" rel="stylesheet" />
  <link href="<?= base_url('assets')?>/css/vs2015.css" rel="stylesheet" />
  <link href="<?= base_url('assets')?>/css/now-ui-dashboard.css?v=1.5.0" rel="stylesheet" />
  <style>
    code{
      height: 200px;
    }
  </style>
</head>

<body>
<nav class="navbar navbar-expand-lg bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Navbar</a>
  </div>
</nav>
<div class="wrapper">
  <div class="container-fluid">
    <div class="row">

      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <div class="col-md-12">
              <div class="form-group">
                <label for=""><h6>Daftar Table</h6></label>
                <select class="form-control" name="list_table">
                  <?php foreach ($list_tables as $k => $v) {
                    if($v == $this->uri->segment(3)){
                      echo '<option value="'.$v.'" selected>'.$v.'</option>';
                    }else{
                      echo '<option value="'.$v.'">'.$v.'</option>';
                    }
                  }?>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="">
                <h6>Controller</h6></label>
                <input type="text"
                  class="form-control" name="controller" placeholder="Contoh : Super Admin" value="<?= $this->uri->segment(4)?>">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="">
                <h6>Session Key</h6></label>
                <input type="text"
                  class="form-control" name="session_key" placeholder="Contoh : level, logged_in" value="<?= $this->uri->segment(5)?>">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="">
                <h6>Session Value</h6></label>
                <input type="text"
                  class="form-control" name="session_value" placeholder="Contoh : Super Admin, Admin" value="<?= $this->uri->segment(6)?>">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="">
                <h6>Input ID Prefix</h6></label>
                <input type="text"
                  class="form-control" name="form_url" placeholder="Insert / Edit / dsb">
              </div>
            </div>
            <div class="col-md-12">
              <label><h6>Daftar Kolom</h6></label>
              <?php foreach ($field_data as $k => $v):?>
                <div class="row">
                  <?php if($v->primary_key == 1 || strpos($v->name, 'id') !== FALSE):?>
                    <div class="col-md-8">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input class="form-check-input checkboxKolom" type="checkbox" value="<?= $v->name?>" data-button="btn_<?= $v->name?>">
                          <span class="form-check-sign"></span>
                          <?= $v->name?>
                        </label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <button class="btn btn-info btn-sm btn-block" id="btn_<?= $v->name?>" disabled data-toggle="modal" data-target="#modal_<?= $v->name?>">Edit</button>
                    </div>
                  <?php else:?>
                    <div class="col-md-8">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input class="form-check-input checkboxKolom" type="checkbox" value="<?= $v->name?>" checked data-button="btn_<?= $v->name?>">
                          <span class="form-check-sign"></span>
                          <?= $v->name?>
                        </label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <button class="btn btn-info btn-sm btn-block" id="btn_<?= $v->name?>" data-toggle="modal" data-target="#modal_<?= $v->name?>">Edit</button>
                    </div>
                  <?php endif;?>
                </div>
              <?php endforeach;?>
            </div>
            <div class="col-md-12">
                <button class="btn btn-primary btn-block" onclick="regenerate_code()">Refresh</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Html - <small class="description">Views</small></h4>
              </div>
              <div class="card-body">
                <ul class="nav nav-pills nav-pills-primary" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#stackedForm" role="tablist">
                      Stacked Form
                    </a>
                  </li><li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#editModal" role="tablist">
                      Edit Modal
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#table" role="tablist">
                      Table
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#js" role="tablist">
                      JS
                    </a>
                  </li>
                </ul>
                <div class="tab-content tab-space">
                  <div class="tab-pane active" id="stackedForm">
                    <textarea  id="stackedFormCode" cols="100" rows="10">
                      <?= $form?> 
                    </textarea>
                    <button class="btn btn-default" data-clipboard-target="#stackedFormCode">
                        copy
                    </button>
                  </div>
                  <div class="tab-pane" id="editModal">
                    <textarea  id="editModalCode" cols="100" rows="10">
                      <?= $edit_modal?> 
                    </textarea>
                    <button class="btn btn-default" data-clipboard-target="#editModalCode">
                        copy
                    </button>
                  </div>
                  <div class="tab-pane" id="table">
                    <textarea  id="tableCode" cols="100" rows="10">
                      <?= $table?>
                    </textarea>
                    <button class="btn btn-default" data-clipboard-target="#tableCode">
                        copy
                    </button>
                  </div>
                  <div class="tab-pane" id="js">
                    <textarea  id="javascriptCode" cols="100" rows="10">
                      <?= $javascript?>
                    </textarea>
                    <button class="btn btn-default" data-clipboard-target="#javascriptCode">
                        copy
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Controller - Model</h4>
              </div>
              <div class="card-body">
                <ul class="nav nav-pills nav-pills-primary" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#controller" role="tablist">
                      Controller
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#model" role="tablist">
                      Model
                    </a>
                  </li>
                </ul>
                <div class="tab-content tab-space">
                  <div class="tab-pane active" id="controller">
                    <textarea  id="controllerCode" cols="100" rows="10">
                      <?= $view_controller?>
                      <?= $insert_controller?>
                      <?= $delete_controller?>
                      <?= $edit_controller?>
                      <?= $get_specific_controller?>
                    </textarea>
                    <button class="btn btn-default" data-clipboard-target="#controllerCode">
                        copy
                    </button>
                  </div>
                  <div class="tab-pane" id="model">
                    <pre>
                      <code class="php" id="modelCode">
                        <?= $model?>
                      </code>
                    </pre>
                    <button class="btn btn-default" data-clipboard-target="#modelCode">
                        copy
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
  
  <?php foreach ($form_config as $k => $v):?>
  <div class="modal fade" id="modal_<?= $v['atribut']['name']?>" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Input Option <?= $v['atribut']['name']?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
          <form id="form_<?= $v['atribut']['name']?>">
            <div class="modal-body">
                <?php foreach ($v['atribut'] as $k_atribut => $v_atribut){
                  if($k_atribut == 'type'){
                    echo '
                      <div class="form-group">
                      <label for="setting_type_'.$k_atribut.'">'.$k_atribut.'</label>
                        <select class="form-control" id="setting_type_'.$k_atribut.'" data-object-key="'.$k_atribut.'">
                        ';
                          if($v_atribut == 'text'){
                            echo '<option value="text" selected>text</option>';
                          }else{
                            echo '<option value="text">text</option>';
                          }
                          
                          if($v_atribut == 'textarea'){
                            echo '<option value="textarea" selected>textarea</option>';
                          }else{
                            echo '<option value="textarea">textarea</option>';
                          }

                          if($v_atribut == 'dropdown'){
                            echo '<option value="dropdown" selected>dropdown</option>';
                          }else{
                            echo '<option value="dropdown">dropdown</option>';
                          }

                          if($v_atribut == 'number'){
                            echo '<option value="number" selected>number</option>';
                          }else{
                            echo '<option value="number">number</option>';
                          }

                          if($v_atribut == 'date'){
                            echo '<option value="date" selected>date</option>';
                          }else{
                            echo '<option value="date">date</option>';
                          }

                          if($v_atribut == 'hidden'){
                            echo '<option value="hidden" selected>hidden</option>';
                          }else{
                            echo '<option value="hidden">hidden</option>';
                          }
                        echo'
                        </select>
                      </div>
                    ';
                  }elseif($k_atribut == 'required'){
                    echo '
                      <p>required</p>
                      <div class="form-check">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input" name="'.$v['atribut']['name'].'_required" data-object-key="'.$k_atribut.'" value="true" checked>
                          True
                        </label>
                      </div>
                      <div class="form-check">
                        <label class="form-check-label">
                          <input type="radio" class="form-check-input" name="'.$v['atribut']['name'].'_required" data-object-key="'.$k_atribut.'" value="false">
                          False
                        </label>
                      </div>
                    ';
                  }else{
                    echo '
                      <label for="setting_type_'.$k_atribut.'">'.$k_atribut.'</label>
                      <div class="form-group">
                          <input type="text" value="'.$v_atribut.'" id="setting_type_'.$k_atribut.'"" class="form-control" data-object-key="'.$k_atribut.'" required>
                      </div>
                    ';
                  }

                }?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" onclick="ubah_input_property('<?= $v['atribut']['name']?>')">Save</button>
            </div>
          </form>
      </div>
    </div>
  </div>

<?php endforeach;?>

  <script src="<?= base_url('assets')?>/js/core/jquery.min.js"></script>
  <script src="<?= base_url('assets')?>/js/core/popper.min.js"></script>
  <script src="<?= base_url('assets')?>/js/core/bootstrap.min.js"></script>
  <script src="<?= base_url('assets')?>/js/plugins/highlight.pack.js"></script>
  <script src="<?= base_url('assets')?>/js/plugins/clipboard.js"></script>
  <script>
    $(document).ready(function () {

      $('.checkboxKolom').click(function(e){
        if(e.target.checked == true){
          $('#'+e.target.dataset.button).removeAttr('disabled');
        }else{
          $('#'+e.target.dataset.button).prop('disabled', true);
        }
      })
      
      $('[name="list_table"]').change(function(e){
        window.location.href = '<?= base_url('Home/index/')?>'+$('[name="list_table"]').val()+'/'+$('[name="controller"]').val()+'/'+$('[name="session_key"]').val()+'/'+$('[name="session_value"]').val();
      })
      new ClipboardJS('.btn');
      
    });
    hljs.initHighlightingOnLoad();

    function ubah_input_property($nama_kolom) {
      $all_input_element = $('#form_'+$nama_kolom+' input, #form_'+$nama_kolom+' select')
      $.each($all_input_element, function (i, v) { 
        // console.log($form_config[$nama_kolom]['atribut']);
        // console.log(v.dataset.objectKey)
        $form_config[$nama_kolom]['atribut'][v.dataset.objectKey] = v.value;
      });
      console.log($form_config);
    }

    function regenerate_code(){
      window.location.href = '<?= base_url('Home/index/')?>'+$('[name="list_table"]').val()+'/'+$('[name="controller"]').val()+'/'+$('[name="session_key"]').val()+'/'+$('[name="session_value"]').val();
    }
  </script>
</body>

</html>