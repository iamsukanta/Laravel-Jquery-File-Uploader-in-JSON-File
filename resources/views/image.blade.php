@extends('layout')
@section('content')
    <div class="container p-5">
        <div class="image-container border border-secondary p-3 h-100">
            <h4>Upload Images</h4>
            <div class="row mt-3">
                <div class="col-3">
                    <button class="btn btn-info w-100" id="uploadImgButton">Upload Image</button>
                </div>
                <div class="col-9">
                    <div class="from-group">
                        <input type="text" class="form-control" name="search_image" id="imageSearch" placeholder="Search"/>
                    </div>
                </div>
            </div>
            <hr>
            <div id="imagePreview" class="row">
                {{-- Show Image Preview --}}
            </div>
        </div>

        <div id="uploadImageModal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content rounded-0 border border-secondary">
                    <div class="modal-header p-2 bg-dark rounded-0">
                        <h5 class="modal-title text-white">Upload Image</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pb-5">
                        <form multipart="form/data" id="uploadImageForm">
                             <div class="input-field">
                                <div class="input-images-1" style="padding-top: .5rem;"></div>
                            </div>

                            <div class="image-title-section text-center pt-2 mt-2">
                                <div class="row">
                                    <div class="col-3 mt-2 text-right">
                                        <h6>Image Title</h6>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control" name="image_title" placeholder="Image Title"/>
                                    </div>
                                </div>

                                <button class="text-center mt-3 btn btn-secondary pl-5 pr-5" type="button" id="uploadImage">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="imagePreviewModal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title preview-image-modal-title text-center"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 preview-modal-body">

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $.ajaxSetup({ cache: false });

        function getImageData() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "/image/data",
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                async:true,
                type:'GET',
                success: function(res) {
                    console.log(res.data);
                    localStorage.setItem('imageData', JSON.stringify(res.data));
                    $("#imagePreview").empty();
                    $.each(res.data,function(index,value){
                        $("#imagePreview").append(`
                            <div class="col-2">
                                <img class="image-preview" titleData="${value.title}" imgData="${value.image}" src="http://localhost:8000/uploads/${value.image}" width="100%" height="130px"/>
                                <h5 class="text-center mt-2">${value.title}</h5>
                                <h6 imageId="${value.id}" fileName="${value.image}" class="text-center imageRemove"><i class="fa fa-trash-o" aria-hidden="true"></i> Remove</h6>
                            </div>
                        `);
                    });
                },
                error:function(err) {
                    console.log(err);
                }
            });
        }

        getImageData();

        $('.input-images-1').imageUploader({
            imagesInputName: 'image',
            maxSize: 5 * 1024 * 1024
        });

        $("#uploadImgButton").click(function(){
            console.log("ok");
            $("#uploadImageModal").modal('show');
        });

        $("#uploadImageForm").submit(function(e){
            return false;
        });

        $('#uploadImage').on('click', function (e) {
            let fileLength = $('input[name^=image]').prop('files').length;
            let file = $('input[name^=image]').prop('files')[fileLength-1];
            console.log(file);
            if($('input[name=image_title]').val() == '') {
                toastr.error('Image Title is Required.', 'Error!');
                return false;
            } else if(!$('input[name^=image]').prop('files').length) {
                toastr.error('Image File is Reuired.', 'Error!');
                return false;
            } else if(file.type != 'image/png') {
                toastr.error('Only Support PNG File.', 'Error!');
                return false;
            } else if(file.size>5242880) {
                toastr.error('Image File is to big.', 'Error!');
                return false;
            } else {
                var formData = new FormData();
                formData.append('image', file);
                formData.append('image_title', $('input[name=image_title]').val());
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "/image/upload",
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    cache: false,
                    enctype: 'multipart/form-data',
                    data : formData,
                    async:true,
                    type:'POST',
                    success: function(res) {
                        $(".uploaded-image").remove();
                        $(".image-uploader").removeClass("has-files");
                        $('input[name=image_title]').val('');
                        $("#uploadImageForm")[0].reset();
                        getImageData();
                    },
                    error:function(err) {
                        console.log(err);
                    }
                });
            }
        });

        $('#imageSearch').keyup(function(){
            $('#imagePreview').html('');
            let storageData = JSON.parse(localStorage.getItem('imageData'));
            let searchField = $('#imageSearch').val();
            let expression = new RegExp(searchField, "i");
            let output = '';
            $.each(storageData, function(key, value){
                if (value.title.search(expression) != -1)
                {
                    output += '<div class="col-2">';
                    output += '<img imgData="'+value.image+'" titleData="'+value.title+'" class="image-preview" width="100%" height="130px" src="http://localhost:8000/uploads/'+value.image+'" alt="'+ value.image +'" />';
                    output += '<h5 class="text-center mt-2">' + value.title + '</h5>';
                    output += '<h6 class="text-center imageRemove" imageId="'+value.id+'" fileName="'+value.image+'"><i class="fa fa-trash-o" aria-hidden="true"></i> Remove</h6>'
                    output += '</div>';
                }
            });
            $('#imagePreview').html(output);
        });

        $('#imagePreview').on('click', '.imageRemove', function() {
            console.log("okk");
            let imgId = $(this).attr('imageId');
            let fileName = $(this).attr('fileName');
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this file!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: `/image/delete/${imgId}/${fileName}`,
                    processData: false,
                    contentType: false,
                    cache: false,
                    async:true,
                    type:'GET',
                    success: function(res) {
                        getImageData();
                        swal.close();
                    },
                    error:function(err) {
                        console.log(err);
                    }
                });
            } else {
                swal.close();
            }
            });
        });

        $('#imagePreview').on('click', '.image-preview', function() {
            console.log("okk");
            $("#imagePreviewModal").modal('show');
            $('.preview-image-modal-title').text('');
            $(".preview-modal-body").empty();
            let titleData = $(this).attr('titleData');
            let imgData = $(this).attr('imgData');
            $('.preview-image-modal-title').text(titleData);

            $(".preview-modal-body").append(`
                <img class="image-preview" src="http://localhost:8000/uploads/${imgData}" width="100%" height="400px"/>
            `);
        });
    });
</script>
@endsection
