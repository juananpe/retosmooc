<?

# quién evalua a... para su grabación .....
$query = "SELECT 
    v.id,
    v.fileId,
    v.filePath,
    v.conversation_id,
    v.user_id AS evaluated,
    g.evaluator AS evaluator,
    c.chat_id
FROM
    voicecache AS v,
    voicegrades AS g,
    conversation AS c
WHERE
    v.question = 1 AND v.challenge = 1
        AND g.question = 1
        AND g.challenge = 1
        AND v.user_id = 45353592
        AND v.selected = 1
        AND v.user_id = g.evaluated
        AND g.sent = 0
	AND c.id = v.conversation_id";

# ver dawe2bot --> broadcast.php
#
