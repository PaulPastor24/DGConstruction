'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
  },
  header: {
    marginBottom: '28px',
    paddingBottom: '20px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  grid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(3, 1fr)',
    gap: '20px',
    '@media (max-width: 1024px)': {
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
    '@media (max-width: 600px)': {
      gridTemplateColumns: '1fr',
    },
  },
  card: {
    padding: '24px',
    borderRadius: '14px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    display: 'flex',
    flexDirection: 'column',
    gap: '16px',
  },
  cardLate: {
    backgroundColor: 'rgba(255, 238, 88, 0.08)',
    borderTop: '3px solid #FFEE58',
  },
  cardAbsent: {
    backgroundColor: 'rgba(255, 0, 0, 0.05)',
    borderTop: '3px solid #CC0000',
  },
  cardHeader: {
    display: 'flex',
    alignItems: 'center',
    gap: '12px',
  },
  icon: {
    fontSize: '24px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '4px',
  },
  workerName: {
    fontSize: '15px',
    fontWeight: 700,
    color: '#333333',
  },
  trade: {
    fontSize: '12px',
    color: '#666666',
    fontWeight: 500,
  },
  status: {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '6px 12px',
    borderRadius: '20px',
    fontSize: '12px',
    fontWeight: 700,
    width: 'fit-content',
  },
  statusLate: {
    backgroundColor: 'rgba(255, 153, 0, 0.15)',
    color: '#996600',
  },
  statusAbsent: {
    backgroundColor: 'rgba(255, 0, 0, 0.1)',
    color: '#CC0000',
  },
  detail: {
    fontSize: '13px',
    color: '#666666',
    fontWeight: 400,
  },
  actions: {
    display: 'flex',
    gap: '12px',
  },
  actionButton: {
    flex: 1,
    padding: '10px',
    border: '1px solid rgba(0, 0, 0, 0.1)',
    borderRadius: '8px',
    backgroundColor: '#FFFFFF',
    color: '#333333',
    cursor: 'pointer',
    fontSize: '13px',
    fontWeight: 600,
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: '#F5F5F0',
      border: '1px solid #2F6B3C',
    },
  },
})

export function LateAndAbsentWorkers() {
  const styles = useStyles()

  const workers = [
    {
      type: 'late',
      icon: '⏱️',
      name: 'James Smith',
      trade: 'Carpenter',
      status: 'Late',
      detail: 'Arrived 15 minutes late',
    },
    {
      type: 'absent',
      icon: '−',
      name: 'William Davis',
      trade: 'Plumber',
      status: 'Absent',
      detail: 'Not marked present today',
    },
  ]

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Late & Absent Workers</div>
        <div className={styles.subtitle}>Workers requiring attention or follow-up actions.</div>
      </div>

      <div className={styles.grid}>
        {workers.map((worker, idx) => (
          <div key={idx} className={`${styles.card} ${worker.type === 'late' ? styles.cardLate : styles.cardAbsent}`}>
            <div className={styles.cardHeader}>
              <div className={styles.icon}>{worker.icon}</div>
              <div className={styles.headerContent}>
                <div className={styles.workerName}>{worker.name}</div>
                <div className={styles.trade}>{worker.trade}</div>
              </div>
            </div>

            <div className={`${styles.status} ${worker.type === 'late' ? styles.statusLate : styles.statusAbsent}`}>
              {worker.status}
            </div>

            <div className={styles.detail}>{worker.detail}</div>

            <div className={styles.actions}>
              <button className={styles.actionButton}>View Details</button>
              <button className={styles.actionButton}>Add Remark</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
