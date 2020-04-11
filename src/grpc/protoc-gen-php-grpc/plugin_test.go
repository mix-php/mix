package main

import (
    "github.com/stretchr/testify/assert"
    "io/ioutil"
    "os"
    "os/exec"
    "strings"
    "testing"
)

// When the environment variable RUN_AS_PROTOC_GEN_PHP is set, we skip running
// tests and instead act as protoc-gen-php. This allows the test binary to
// pass itself to protoc.
func init() {
    if os.Getenv("RUN_AS_PROTOC_GEN_PHP") != "" {
        main()
        os.Exit(0)
    }
}

func protoc(t *testing.T, args []string) {
    cmd := exec.Command("protoc", "--plugin=protoc-gen-php-grpc="+os.Args[0])
    cmd.Args = append(cmd.Args, args...)
    cmd.Env = append(os.Environ(), "RUN_AS_PROTOC_GEN_PHP=1")
    out, err := cmd.CombinedOutput()

    if len(out) > 0 || err != nil {
        t.Log("RUNNING: ", strings.Join(cmd.Args, " "))
    }

    if len(out) > 0 {
        t.Log(string(out))
    }

    if err != nil {
        t.Fatalf("protoc: %v", err)
    }
}

func substr(s string, pos, length int) string {
    runes := []rune(s)
    l := pos + length
    if l > len(runes) {
        l = len(runes)
    }
    return string(runes[pos:l])
}

func Test_Simple(t *testing.T) {
    workdir := substr(os.Args[0], 0, strings.LastIndex(os.Args[0], "/"))
    tmpdir, err := ioutil.TempDir(workdir, "proto-test.")
    if err != nil {
        t.Fatal(err)
    }
    defer os.RemoveAll(tmpdir)

    args := []string{
        "-Itestdata",
        "--php-grpc_out=" + tmpdir,
        "simple/simple.proto",
    }
    protoc(t, args)

    assertEqualFiles(
        t,
        workdir+"/testdata/simple/TestSimple/SimpleServiceInterface.php",
        tmpdir+"/TestSimple/SimpleServiceInterface.php",
    )
}

func Test_PhpNamespaceOption(t *testing.T) {
    workdir, _ := os.Getwd()
    tmpdir, err := ioutil.TempDir("", "proto-test")
    if err != nil {
        t.Fatal(err)
    }
    defer os.RemoveAll(tmpdir)

    args := []string{
        "-Itestdata",
        "--php-grpc_out=" + tmpdir,
        "php_namespace/service.proto",
    }
    protoc(t, args)

    assertEqualFiles(
        t,
        workdir+"/testdata/php_namespace/Test/CustomNamespace/ServiceInterface.php",
        tmpdir+"/Test/CustomNamespace/ServiceInterface.php",
    )
}

func Test_UseImportedMessage(t *testing.T) {
    workdir, _ := os.Getwd()
    tmpdir, err := ioutil.TempDir("", "proto-test")
    if err != nil {
        t.Fatal(err)
    }
    defer os.RemoveAll(tmpdir)

    args := []string{
        "-Itestdata",
        "--php-grpc_out=" + tmpdir,
        "import/service.proto",
    }
    protoc(t, args)

    assertEqualFiles(
        t,
        workdir+"/testdata/import/Import/ServiceInterface.php",
        tmpdir+"/Import/ServiceInterface.php",
    )
}

func Test_PhpNamespaceOptionInUse(t *testing.T) {
    workdir, _ := os.Getwd()
    tmpdir, err := ioutil.TempDir("", "proto-test")
    if err != nil {
        t.Fatal(err)
    }
    defer os.RemoveAll(tmpdir)
    args := []string{
        "-Itestdata",
        "--php-grpc_out=" + tmpdir,
        "import_custom/service.proto",
    }
    protoc(t, args)

    assertEqualFiles(
        t,
        workdir+"/testdata/import_custom/Test/CustomImport/ServiceInterface.php",
        tmpdir+"/Test/CustomImport/ServiceInterface.php",
    )
}

func Test_UseOfGoogleEmptyMessage(t *testing.T) {
    workdir, _ := os.Getwd()
    tmpdir, err := ioutil.TempDir("", "proto-test")
    if err != nil {
        t.Fatal(err)
    }
    defer os.RemoveAll(tmpdir)
    args := []string{
        "-Itestdata",
        "--php-grpc_out=" + tmpdir,
        "use_empty/service.proto",
    }
    protoc(t, args)

    assertEqualFiles(
        t,
        workdir+"/testdata/use_empty/Test/ServiceInterface.php",
        tmpdir+"/Test/ServiceInterface.php",
    )
}

func assertEqualFiles(t *testing.T, original, generated string) {
    assert.FileExists(t, generated)

    originalData, err := ioutil.ReadFile(original)
    if err != nil {
        t.Fatal("Can't find original file for comparison")
    }

    generatedData, err := ioutil.ReadFile(generated)
    if err != nil {
        t.Fatal("Can't find generated file for comparison")
    }

    // every OS has a special boy
    r := strings.NewReplacer("\r\n", "", "\n", "")
    assert.Equal(t, r.Replace(string(originalData)), r.Replace(string(generatedData)))
}
