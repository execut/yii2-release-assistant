<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 10/2/17
 * Time: 10:07 AM
 */

namespace execut\release;


use yii\console\Controller;

class ReleaseController extends Controller
{
    public $level = 2;
    public $message = 'Release';
    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['level', 'message']
        );
    }

    /**
     * @inheritdoc
     * @since 2.0.8
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'l' => 'level',
            'm' => 'message',
        ]);
    }

    public function actionIndex() {
        $commands = [
            'git checkout master',
            'git pull',
            'git add .',
            'git commit -m \'' . $this->message . '\'',
            'git push',
            'git tag ' . $this->getNextVersion(),
            'git push --tags',
        ];

        foreach ($commands as $command) {
            exec($command, $out, $result);
            if ($result !== 0 && $result !== 1) {
                break;
            }
        }
    }

    protected function getNextVersion() {
        exec('git tag -l', $out);
        uasort($out, function ($a, $b) {
            $aParts = explode('.', $a);
            $bParts = explode('.', $b);
            foreach ($aParts as $key => $aPart) {
                if ($aPart > $bParts[$key]) {
                    return true;
                }
                if ($bParts[$key] > $aPart) {
                    return false;
                }
            }
        });

        $currentVersion = end($out);
        if (empty($currentVersion)) {
            $currentVersion = '0.0.1';
        }

        $parts = explode('.', $currentVersion);
        if ($this->level >= count($parts)) {
            throw new \Exception('Wrong level');
        }

        $parts[$this->level]++;
        for ($key = $this->level + 1; $key < count($parts); $key++) {
            $parts[$key] = 0;
        }

        $nextVersion = implode('.', $parts);

        return $nextVersion;
    }
}