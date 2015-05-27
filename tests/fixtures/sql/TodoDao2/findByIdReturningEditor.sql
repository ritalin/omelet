select 
    t.*,
    108 as creator_id, 'Foo' as creator_name
from todo t
where id = :key
