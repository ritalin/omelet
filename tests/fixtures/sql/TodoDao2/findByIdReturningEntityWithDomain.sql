select t.*, 1 as hidden
from todo t
where id = :key_value
