

RealityCapture.exe -load C:\Users\halusag\Documents\processed\PlainProject.rcproj -newScene^
-addFolder C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00027936\clipped\^
-align -setReconstructionRegionAuto^
-exportReconstructionRegion C:\Users\halusag\Documents\processed\myBox.rcbox^
-mvs -calculateVertexColors^
-mvsHigh -calculateTexture^
-save C:\Documents\processed\PlainProject.rcproj^
-exportModel "modelName"^
C:\Users\halusag\Documents\processed\PlainProject\mesh.obj -quit^
C:\Users\halusag\Documents\processed\PlainProject\params.xml -quit



set PATH=%PATH%;C:\Program Files\Capturing Reality\RealityCapture\
set MyPath=C:\Users\halusag\Documents\processed
set ScansPath=C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00027936\clipped
set XMLParamsPath=C:\Users\halusag\Documents

RealityCapture.exe -newScene -addFolder C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00027936\clipped\ -align -setReconstructionRegionAuto -mvs -simplify 80000 -smooth -calculateVertexColors -calculateTexture -selectMaximalComponent -exportModel "Model2" C:\Users\halusag\Documents\processed\mesh.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\processed\model.rcproj

RealityCapture.exe -newScene -addFolder C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00027936\clipped\ -align -setReconstructionRegionAuto -mvs -simplify 80000 -smooth -calculateVertexColors -calculateTexture -exportModel "model2" C:\Users\halusag\Documents\PilotProjectFiles\model2.obj -save C:\Users\halusag\Documents\PilotProjectFiles\model2








RealityCapture.exe -newScene -addFolder C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00027936\clipped\ -align -setReconstructionRegionAuto -mvsHigh -calculateVertexColors -calculateTexture -selectMaximalComponent -exportModel "Model 1" C:\Users\halusag\Documents\PilotProjectFiles\model1.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\PilotProjectFiles\model1

RealityCapture.exe -newScene -addFolder C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00027936\clipped_group_2\ -align -setReconstructionRegionAuto -mvsHigh -calculateVertexColors -calculateTexture -selectMaximalComponent -exportModel "Model 2" C:\Users\halusag\Documents\PilotProjectFiles\model2.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\PilotProjectFiles\model2




mops --write_config C:\Users\halusag\Documents\PilotProjectFiles\iuv_config.json

mops --read_config C:\Users\halusag\Documents\PilotProjectFiles\iuv_config.json -i C:\Users\halusag\Documents\PilotProjectFiles\model1.obj --duplicate -d 10000 --segment --unwrap --bake_maps -w C:\Users\halusag\Documents\PilotProjectFiles\outputWeb2

mops --read_config C:\Users\halusag\Documents\PilotProjectFiles\iuv_config.json -i C:\Users\halusag\Documents\PilotProjectFiles\model2.obj --Make_Compact -w outputWeb




mops -i C:\Users\halusag\Documents\PilotProjectFiles\model2.obj --segment --parameterize -e C:\Users\halusag\Documents\PilotProjectFiles\model2-UV.obj

mops -i C:\Users\halusag\Documents\PilotProjectFiles\model1.obj --Make_Compact -w C:\Users\halusag\Documents\PilotProjectFiles\outputWeb1


mops --read_config C:\Users\halusag\Documents\PilotProjectFiles\iuv_config.json -i C:\Users\halusag\Documents\PilotProjectFiles\model2.obj --Make_Compact -w C:\Users\halusag\Documents\PilotProjectFiles\outputWeb2







C:\Users\halusag\Documents\Tusk_Test_Photos\JPEG\clipped

set PATH=%PATH%;C:\Program Files\Capturing Reality\RealityCapture\

RealityCapture.exe -newScene -addFolder C:\Users\halusag\Documents\Tusk_Test_Photos\JPEG\clipped\ -align -setReconstructionRegionAuto -mvsHigh -calculateVertexColors -calculateTexture -selectMaximalComponent -exportModel "Tusk_Test_Photos" C:\Users\halusag\Documents\Tusk_Test_Photos\processed\Tusk_Test_Photos_mesh.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\Tusk_Test_Photos

RealityCapture.exe -newScene -addFolder C:\Users\halusag\Documents\Tusk_Test_Photos\JPEG\clipped\ -align -selectMaximalComponent -setReconstructionRegionAuto -mvsHigh -calculateVertexColors -calculateTexture -exportModel "Model1" C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1_mesh.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1

mops -i C:\Users\halusag\Documents\Tusk_Test_Photos\Tusk_Test_Photos_mesh.obj --Make_Compact -w C:\Users\halusag\Documents\Tusk_Test_Photos\processed\webready



FIRST ALIGN, EXPORT XMP, THEN QUIT

RealityCapture.exe -addFolder C:\Users\halusag\Documents\Tusk_Test_Photos\JPEG\clipped\ -align -setReconstructionRegionAuto -exportXmp -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\AlignedProject.rcproj -quit



XXXXX RealityCapture.exe -load C:\Users\halusag\Documents\Tusk_Test_Photos\processed\AlignedProject.rcproj -setReconstructionRegionAuto -exportReconstructionRegion C:\Users\halusag\Documents\Tusk_Test_Photos\processed\myBox.rcbox -quit


PROCESS

XXXXX RealityCapture.exe -load C:\Users\halusag\Documents\Tusk_Test_Photos\processed\AlignedProject.rcproj -selectMaximalComponent -exportComponent C:\Users\halusag\Documents\Tusk_Test_Photos\processed\max -quit

XXXXX for %s in ("C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent *.rcalign") do ( RealityCapture.exe -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\%s" -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\NewProject.rcproj -quit )

XXXXX RealityCapture.exe -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent 0.rcalign" -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\NewProject.rcproj -quit

XXXXX RealityCapture.exe -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent 1.rcalign" -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\NewProject.rcproj -quit

XXXXX RealityCapture.exe -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent 2.rcalign" -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\NewProject.rcproj -quit

XXXXX RealityCapture.exe -load C:\Users\halusag\Documents\Tusk_Test_Photos\processed\AlignedProject.rcproj -selectMaximalComponent -setReconstructionRegionAuto C:\Users\halusag\Documents\Tusk_Test_Photos\processed\myBox.rcbox -mvsPreview -previewVertexColors -mvs -calculateVertexColors -mvsHigh -calculateTexture -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\NewProject.rcproj -quit

XXXXX RealityCapture.exe -load C:\Users\halusag\Documents\Tusk_Test_Photos\processed\NewProject.rcproj -mvs -simplify 100000 -smooth -exportModel "Model 1" C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1 -quit







RealityCapture.exe -load C:\Users\halusag\Documents\Tusk_Test_Photos\processed\AlignedProject.rcproj -selectMaximalComponent C:\Users\halusag\Documents\Tusk_Test_Photos\processed\myBox.rcbox -mvs -calculateVertexColors -calculateTexture -exportModel "Model 1" C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1 -quit

mops -i C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1.obj --Make_Compact -w C:\Users\halusag\Documents\Tusk_Test_Photos\processed\webready






RealityCapture.exe -addFolder C:\Users\halusag\Documents\Tusk_Test_Photos\JPEG\ -align -setReconstructionRegionAuto -exportXmp -selectMaximalComponent -exportComponent C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\max -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\model1

RealityCapture.exe -load C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\model1.rcproj -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent 0.rcalign" -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent 1.rcalign" -importComponent "C:\Users\halusag\Documents\Tusk_Test_Photos\processed\maxComponent 2.rcalign" C:\Users\halusag\Documents\Tusk_Test_Photos\processed\max C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\myBox.rcbox -mvs -calculateVertexColors -calculateTexture -exportModel "Model 1" C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\model1.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\model1 -quit

mops -i C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1.obj --Make_Compact -w C:\Users\halusag\Documents\Tusk_Test_Photos\processed2\webready






set PATH=%PATH%;C:\Program Files\Capturing Reality\RealityCapture\

RealityCapture.exe -addFolder C:\Users\halusag\Documents\PilotProjectFiles\8594801b02e7305a9c5a8411ebec0d04\clipped\ -align -setReconstructionRegionAuto -exportXmp -selectMaximalComponent -mvs -calculateVertexColors -calculateTexture -exportModel "Model 1" C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00033475_processed\model1.obj C:\Users\halusag\Documents\params.xml -save C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00033475_processed\model1 -quit

mops -i C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00033475_processed\model1.obj --Make_Compact -w C:\Users\halusag\Documents\PilotProjectFiles\nmnh-USNM_PAL_00033475_processed\webready





$mainDirectory = "C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04\";

foreach($directory in Get-ChildItem $mainDirectory)
{
  $directoryPath = $mainDirectory + $directory;
  mkdir $directoryPath\clipped
  foreach($file in Get-ChildItem $directoryPath -Filter *.jpg)
  {
  	 magick -quiet $directoryPath/$file -black-threshold 10% -quality 100 $directoryPath\clipped\$file
  }

}





US\SI-3DDigiP01

mkdir "C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\";

set PATH=%PATH%;C:\Program Files\Capturing Reality\RealityCapture\

runas /user:SI-3DDigiP01\halusag RealityCapture.exe -addFolder C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04\clipped

RealityCapture.exe -addFolder C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04\clipped -align -setReconstructionRegionAuto -exportXmp -selectMaximalComponent -mvs -calculateVertexColors -calculateTexture -exportModel "Model 1" C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\mesh.obj C:\JobBoxProcess\settings\params.xml -save C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\mesh -quit



INSTANTUV WITH DEFAULT SETTINGS

mops -i C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\mesh.obj --Make_Compact -w C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\webready

INSTANTUV WITH CUSTOM SETTINGS

mops --read_config C:\JobBoxProcess\settings\iuv_config.json -i C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\mesh.obj --Make_Compact -w C:\JobBoxProcess\8594801b02e7305a9c5a8411ebec0d04_processed\webready



mops --read_config C:\Users\halusag\Documents\Tusk_Test_Photos\processed\iuv_config.json -i C:\Users\halusag\Documents\Tusk_Test_Photos\processed\model1.obj -w C:\Users\halusag\Documents\Tusk_Test_Photos\processed\webready



mops --read_config C:\JobBoxProcess\settings\iuv_config.json -i C:\Users\halusag\Downloads\USNM260582_Cranium_-600_dec.ply -e out_2.obj

mops --read_config C:\JobBoxProcess\settings\iuv_config.json -i C:\Users\halusag\Downloads\USNM260582_Cranium_-600_dec.ply --Create_UV_Atlas -e out_3.obj

mops -i C:\Users\halusag\Downloads\USNM260582_Cranium_-600_dec.ply -e out_4.obj