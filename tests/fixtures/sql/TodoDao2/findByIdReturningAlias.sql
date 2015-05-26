select 
    t.id as todo_id, 
    t.todo, t.created
from todo t
where id = :key
