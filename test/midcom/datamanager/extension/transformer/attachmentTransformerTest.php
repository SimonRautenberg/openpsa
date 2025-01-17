<?php
/**
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace midcom\datamanager\test;

use openpsa_testcase;
use midcom;
use midcom\datamanager\extension\transformer\attachmentTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class attachmentTransformerTest extends openpsa_testcase
{
    /**
     * @return attachmentTransformer
     */
    private function get_transformer()
    {
        $config = [
            'widget_config' => [
                'show_description' => false,
                'show_title' => true
            ]
        ];
        return new attachmentTransformer($config);
    }

    /**
     * @dataProvider provider_transform
     */
    public function test_transform($input, $expected)
    {
        $transformer = $this->get_transformer();
        $this->assertEquals($expected, $transformer->transform($input));
    }

    /**
     * @dataProvider provider_transform
     */
    public function test_reverseTransform($expected, $input)
    {
        $transformer = $this->get_transformer();
        $this->assertEquals($expected, $transformer->reverseTransform($input));
    }

    public function provider_transform()
    {
        $topic = $this->create_object(\midcom_db_topic::class);
        midcom::get()->auth->request_sudo('midcom.datamanager');
        $att = $topic->create_attachment('test', 'test', 'text/plain');
        $handle = $att->open('w');
        fwrite($handle, 'test');
        $time = filemtime($att->get_path());
        $att->close();

        midcom::get()->auth->drop_sudo('midcom.datamanager');

        return [
           [null, null],
           [$att, [
               'object' => $att,
               'filename' => 'test',
               'description' => 'test',
               'title' => 'test',
               'mimetype' => 'text/plain',
               'url' => '/midcom-serveattachmentguid-' . $att->guid . '/test',
               'id' => $att->id,
               'guid' => $att->guid,
               'filesize' => 4,
               'formattedsize' => '4 Bytes',
               'lastmod' => $time,
               'isoformattedlastmod' => date('Y-m-d H:i:s', $time),
               'size_x' => null,
               'size_y' => null,
               'size_line' => null,
               'score' => 0,
               'identifier' => $att->guid,
               'file' => null
           ]]
        ];
    }

    public function test_upload()
    {
        $transformer = $this->get_transformer();

        $path = midcom::get()->config->get('midcom_tempdir') . '/test';
        file_put_contents($path, 'test');
        $time = filemtime($path);
        $file = new UploadedFile($path, 'test.txt');

        $input = [
            'title' => null,
            'identifier' => null,
            'file' => $file
        ];

        $rt_expected = new \midcom_db_attachment();
        $rt_expected->name = 'test.txt';
        $rt_expected->title = 'test.txt';
        $rt_expected->mimetype = 'text/plain';
        $rt_expected->location = $path;

        $this->assertEquals($rt_expected, $transformer->reverseTransform($input));

        $t_expected = [
            'object' => $rt_expected,
            'filename' => 'test.txt',
            'description' => 'test.txt',
            'title' => 'test.txt',
            'mimetype' => 'text/plain',
            'url' => '',
            'id' => 0,
            'guid' => '',
            'filesize' => 4,
            'formattedsize' => '4 Bytes',
            'lastmod' => $time,
            'isoformattedlastmod' => date('Y-m-d H:i:s', $time),
            'size_x' => null,
            'size_y' => null,
            'size_line' => null,
            'score' => 0,
            'identifier' => 'test',
            'file' => $file
        ];

        $this->assertEquals($t_expected, $transformer->transform($rt_expected));
    }

    public function test_upload_from_tmpfile()
    {
        $transformer = $this->get_transformer();

        $path = midcom::get()->config->get('midcom_tempdir') . '/tmpfile-9dc7ded0fb8f77a341cda2ebd4a698df';
        file_put_contents($path, 'test');
        $time = filemtime($path);

        $input = [
            'title' => 'test.txt',
            'identifier' => 'tmpfile-9dc7ded0fb8f77a341cda2ebd4a698df',
            'file' => null
        ];
        $file = new UploadedFile($path, 'test.txt');

        $rt_expected = new \midcom_db_attachment();
        $rt_expected->name = 'test.txt';
        $rt_expected->title = 'test.txt';
        $rt_expected->location = $path;

        $this->assertEquals($rt_expected, $transformer->reverseTransform($input));

        $t_expected = [
            'object' => $rt_expected,
            'filename' => 'test.txt',
            'description' => 'test.txt',
            'title' => 'test.txt',
            'mimetype' => '',
            'url' => '',
            'id' => 0,
            'guid' => '',
            'filesize' => 4,
            'formattedsize' => '4 Bytes',
            'lastmod' => $time,
            'isoformattedlastmod' => date('Y-m-d H:i:s', $time),
            'size_x' => null,
            'size_y' => null,
            'size_line' => null,
            'score' => 0,
            'identifier' => 'tmpfile-9dc7ded0fb8f77a341cda2ebd4a698df',
            'file' => $file
        ];

        $this->assertEquals($t_expected, $transformer->transform($rt_expected));
    }
}
