update todo set
    todo = :todo,
    created = :created
where
    id = :id
