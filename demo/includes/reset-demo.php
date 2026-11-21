<?php

/** Restore the playground's sample records in the prefixed tables only. */
function resetDemoData(PDO $connection)
{
    $databaseName = (string) $connection->query('SELECT DATABASE()')->fetchColumn();
    $lockName = 'apirest_demo_' . substr(hash('sha256', $databaseName), 0, 24);
    $lock = $connection->prepare('SELECT GET_LOCK(:lock_name, 10)');
    $lock->execute(['lock_name' => $lockName]);

    if ((string) $lock->fetchColumn() !== '1') {
        throw new RuntimeException('The demo reset is already running.');
    }

    try {
        $connection->beginTransaction();
        $connection->exec('DELETE FROM apirest_course_subject');
        $connection->exec('DELETE FROM apirest_courses');
        $connection->exec('DELETE FROM apirest_subjects');

        $connection->exec("INSERT INTO apirest_subjects (id, name) VALUES
            (1, 'Mathematics'),
            (2, 'Italian'),
            (3, 'Philosophy')");
        $connection->exec("INSERT INTO apirest_courses (id, name, places) VALUES
            (1, 'First course', 30),
            (2, 'Second course', 10)");
        $connection->exec('INSERT INTO apirest_course_subject (course_id, subject_id) VALUES
            (1, 1), (1, 2), (2, 1), (2, 3)');

        $connection->commit();
    } catch (Throwable $error) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $error;
    } finally {
        $release = $connection->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $release->execute(['lock_name' => $lockName]);
    }
}
